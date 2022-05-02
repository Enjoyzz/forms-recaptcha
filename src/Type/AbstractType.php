<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha\Type;

use Enjoys\Forms\Captcha\reCaptcha\TypeInterface;
use Enjoys\Forms\Elements\Captcha;

abstract class AbstractType implements TypeInterface
{
    public function __construct(private Captcha $element)
    {
    }

    public function getElement(): Captcha
    {
        return $this->element;
    }
}
