<?php
declare(strict_types=1);


namespace Enjoys\Forms\Captcha\reCaptcha\Language;


use Enjoys\Forms\Captcha\reCaptcha\ReCaptchaLanguageResponseInterface;

final class En implements ReCaptchaLanguageResponseInterface
{
    public function getErrorCode(string $code): string
    {
        return ReCaptchaLanguageResponseInterface::DEFAULT_ERROR_CODES[$code] ?? '';
    }

    public function __toString()
    {
        return 'en';
    }
}
