<?php

declare(strict_types=1);


namespace Enjoys\Forms\Captcha\reCaptcha\Type;


use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Captcha\reCaptcha\TypeInterface;
use Enjoys\Forms\Elements\Captcha;

class V2 implements TypeInterface
{
    public function __construct(private Captcha $element)
    {
    }

    public function render(): string
    {
        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="g-recaptcha" data-sitekey="%s"></div>
HTML,
            $this->element->getCaptcha()->getPublicKey()
        );
    }
}
