<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace apps\components;

use apps\models;
use apps\libraries\Constant;

use Rid\Base\Component;
use Rid\Helpers\JWTHelper;

class Auth extends Component
{
    protected $cur_user;
    protected $cur_user_session_id;

    protected $grant;

    public function onRequestBefore()
    {
        parent::onRequestBefore(); // TODO: Change the autogenerated stub

        $this->cur_user = null;
        $this->cur_user_session_id = null;
    }

    /**
     * @param string $grant
     * @param bool $flush
     * @return models\User|bool return False means this user is anonymous
     */
    public function getCurUser($grant = 'cookies', $flush = false)
    {
        if (is_null($this->cur_user) || $flush) {
            $this->grant = $grant;
            $this->cur_user = $this->loadCurUser($grant);
        }
        return $this->cur_user;
    }

    public function getCurUserSessionId(): string
    {
        return $this->cur_user_session_id ?? '';
    }

    public function getGrant(): string
    {
        return $this->grant ?? '';
    }

    /**
     * @param string $grant
     * @return models\User|boolean
     */
    protected function loadCurUser($grant = 'cookies')
    {
        $user_id = false;
        if ($grant == 'cookies') $user_id = $this->loadCurUserIdFromCookies();
        elseif ($grant == 'passkey') $user_id = $this->loadCurUserIdFromPasskey();

        if ($user_id !== false && is_int($user_id) && $user_id > 0) {
            $user_id = intval($user_id);
            $curuser = app()->site->getUser($user_id);
            if ($curuser->getStatus() !== models\User::STATUS_DISABLED)  // user status shouldn't be disabled
                return $curuser;
        }

        return false;
    }

    protected function loadCurUserIdFromCookies()
    {
        $user_session = app()->request->cookie(Constant::cookie_name);
        if (is_null($user_session)) return false;  // quick return when cookies is not exist

        $payload = JWTHelper::decode($user_session);
        if ($payload === false) return false;
        if (!isset($payload['jti']) || !isset($payload['user_id'])) return false;

        // Check if user lock access ip ?
        if (isset($payload['secure_login_ip'])) {
            $now_ip_crc = sprintf('%08x', crc32(app()->request->getClientIp()));
            if (strcasecmp($payload['secure_login_ip'], $now_ip_crc) !== 0) return false;
        }

        // Verity $jti is force expired or not by checking mapUserSessionToId
        $expired_check = app()->redis->zScore(Constant::mapUserSessionToId, $payload['jti']);
        if ($expired_check === false) {  // session is not see in Zset Cache (may lost or first time init), load from database ( Lazy load... )
            $uid = app()->pdo->createCommand('SELECT `uid` FROM `user_session_log` WHERE sid = :sid AND `expired` != 1 LIMIT 1')->bindParams([
                'sid' => $payload['jti']
            ])->queryScalar();
            app()->redis->zAdd(Constant::mapUserSessionToId, $uid ?: 0, $payload['jti']);  // Store 0 if session -> uid is invalid
            if ($uid === false) return false;  // this session is not exist or marked as expired
        } elseif ($expired_check != $payload['user_id']) return false;    // may return (double) 0 , which means already make invalid ; or it check if user obtain this session (may Overdesign)

        $this->cur_user_session_id = $payload['jti'];

        // Check if user want secure access but his environment is not secure
        if (!app()->request->isSecure() &&                     // if User requests is not secure , then
            ((isset($payload['ssl']) && $payload['ssl'] &&     //   if User want secure access
                    config('security.ssl_login') > 0          //      and if Our site support ssl feature
                ) || config('security.ssl_login') > 1)) {  //   or if  Our site FORCE enabled ssl feature
            app()->response->redirect(str_replace('http://', 'https://', app()->request->fullUrl()));
            app()->response->setHeader('Strict-Transport-Security', 'max-age=1296000; includeSubDomains');
        }

        return $payload['user_id'];
    }

    protected function loadCurUserIdFromPasskey()
    {
        $passkey = app()->request->get('passkey');
        if (is_null($passkey)) return false;

        $user_id = app()->redis->zScore(Constant::mapUserPasskeyToId, $passkey);
        if (false === $user_id) {
            $user_id = app()->pdo->createCommand('SELECT `id` FROM `users` WHERE `passkey` = :passkey LIMIT 1;')->bindParams([
                'passkey' => $passkey
            ])->queryScalar() ?: 0;
            app()->redis->zAdd(Constant::mapUserPasskeyToId, $user_id, $passkey);
        }

        return $user_id > 0 ? $user_id : false;
    }
}
