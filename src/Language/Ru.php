<?php
declare(strict_types=1);


namespace Enjoys\Forms\Captcha\reCaptcha\Language;


use Enjoys\Forms\Captcha\reCaptcha\ReCaptchaLanguageResponseInterface;

final class Ru implements ReCaptchaLanguageResponseInterface
{
    private const  ERROR_CODES = [
        'missing-input-secret' => 'Секретный параметр отсутствует.',
        'invalid-input-secret' => 'Секретный параметр является недопустимым или искаженным.',
        'missing-input-response' => 'Параметр ответа отсутствует.',
        'invalid-input-response' => 'Параметр ответа является недопустимым или искаженным.',
        'bad-request' => 'Запрос недействителен или искажен.',
        'timeout-or-duplicate' => 'Ответ больше не действителен: либо он слишком стар, либо использовался ранее.',
    ];

    public function getErrorCode(string $code): string
    {
        return self::ERROR_CODES[$code] ?? ReCaptchaLanguageResponseInterface::DEFAULT_ERROR_CODES[$code] ?? '';
    }

    public function __toString()
    {
        return 'ru';
    }
}
