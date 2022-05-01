<?php

declare(strict_types=1);


namespace Enjoys\Forms\Captcha\reCaptcha\Type;


use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Elements\Captcha;

class V2
{
    public function __construct(private reCaptcha $reCaptcha, private Captcha $element)
    {
    }

    public function __invoke()
    {
        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="g-recaptcha" data-sitekey="%s"></div>
HTML,
            $this->reCaptcha->getPublicKey()
        );
    }
}
