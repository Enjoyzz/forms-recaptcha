<?php

declare(strict_types=1);


namespace Enjoys\Forms\Captcha\reCaptcha\Type;


class V3 extends V2Invisible
{
    protected function getAttributes(): array
    {
        $attributes = parent::getAttributes();
        $attributes['data-action'] = $this->getElement()->getCaptcha()->getOption('data-action', 'submit');
        return $attributes;
    }
}
