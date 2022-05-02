<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha\Type;

class V2 extends AbstractType
{
    public function render(): string
    {
        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js?hl=%s" async defer></script>
<div %s></div>
HTML,
            $this->getElement()->getCaptcha()->getLanguage(),
            implode(
                ' ',
                array_map(function ($attribute, $value) {
                    return sprintf('%s="%s"', $attribute, $value);
                }, array_keys($this->getAttributes()), array_values($this->getAttributes()))
            )
        );
    }

    protected function getAttributes(): array
    {
        return array_filter([
            'class' => 'g-recaptcha',
            'data-sitekey' => $this->getElement()->getCaptcha()->getPublicKey(),
            'data-theme' => $this->getElement()->getCaptcha()->getOption('data-theme'),
            'data-size' => $this->getElement()->getCaptcha()->getOption('data-size'),
            'data-tabindex' => $this->getElement()->getCaptcha()->getOption('data-tabindex'),
            'data-callback' => $this->getElement()->getCaptcha()->getOption('data-callback'),
            'data-expired-callback' => $this->getElement()->getCaptcha()->getOption('data-expired-callback'),
            'data-error-callback' => $this->getElement()->getCaptcha()->getOption('data-error-callback'),
        ], function ($value) {
            return $value !== null;
        });
    }
}
