<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace App\Models\Form\Traits;

use App\Entity\User\User;

trait isValidUserTrait
{
    public int $id;  // User Id
    protected User $user;

    public static function inputRules(): array
    {
        return [
            'id' => 'Required | Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return ['isExistUser'];
    }

    /** @noinspection PhpUnused */
    protected function isExistUser()
    {
        $test_uid = $this->getInput('id');
        $uid = container()->get('pdo')->prepare('SELECT id FROM users WHERE id = :uid LIMIT 1')->bindParams([
            'uid' => $test_uid
        ])->queryScalar();
        if ($uid === false) {
            $this->buildCallbackFailMsg('User', 'The user id (' . $uid . ') is not exist in our database');
        }
        $this->user = container()->get(\App\Entity\User\UserFactory::class)->getUserById($uid);
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
