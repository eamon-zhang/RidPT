<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:58
 */

namespace Rid\Validators;

/**
 * When use this Trait, Add input which name is `captcha` in your Form,
 * which you can simply call it by `<?= $this->insert('layout/captcha') ?>` in template
 * Add add callback function `validateCaptcha` in callbackRules()
 *
 * Trait CaptchaTrait
 * @package Rid\Validators
 * @property-read string $captcha
 */
trait CaptchaTrait
{
    /** @noinspection PhpUnused */
    protected function validateCaptcha()
    {
        $captchaText = container()->get('session')->get('captchaText');
        if (strcasecmp($this->captcha, $captchaText) != 0) {
            $this->buildCallbackFailMsg('CAPTCHA', 'CAPTCHA verification failed.');
            return;
        }
    }
}
