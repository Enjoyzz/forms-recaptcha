<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha;

interface TypeInterface
{
    public function render(): string;
}
