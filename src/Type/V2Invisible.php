<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha\Type;

use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Captcha\reCaptcha\TypeInterface;
use Enjoys\Forms\Elements\Captcha;

class V2Invisible implements TypeInterface
{
    public function __construct(private Captcha $element)
    {
    }

    public function render(): string
    {
        $form = $this->element->getForm();

        if (null === $formAttributeId = $form->getAttribute('id')?->getValueString()) {
            throw new \InvalidArgumentException('Set attribute form id');
        }


        $submitElement = $form->getElement($this->element->getCaptcha()->getOption('submitEl', 'submit'));

        if ($submitElement === null) {
            throw new \InvalidArgumentException('Set correctly submit element name. Option is `submitEl`');
        }

        if ($submitElement->getAttribute('id')->getValueString() === 'submit') {
            throw new \InvalidArgumentException(
                'The submit button ID should not be `submit`, please set a different id for the submit button'
            );
        }

        $submitElement->addAttributes(
            AttributeFactory::createFromArray($this->getAttributes())
        );

        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js?hl=%s" async defer></script>
 <script>
   function onSubmit(token) {
     document.getElementById("%s").submit();
   }
 </script>
HTML,
            $this->element->getCaptcha()->getLanguage(),
            $formAttributeId
        );
    }


    protected function getAttributes(): array
    {
        $attributes = [
            'class' => 'g-recaptcha',
            'data-sitekey' => $this->element->getCaptcha()->getPublicKey(),
            'data-callback' => $this->element->getCaptcha()->getOption('data-callback', 'onSubmit')
        ];

        if ($this->element->getCaptcha()->getOption('data-badge') !== null) {
            $attributes['data-badge'] = $this->element->getCaptcha()->getOption('data-badge');
        }
        if ($this->element->getCaptcha()->getOption('data-size') !== null) {
            $attributes['data-size'] = $this->element->getCaptcha()->getOption('data-size');
        }
        if ($this->element->getCaptcha()->getOption('data-tabindex') !== null) {
            $attributes['data-tabindex'] = $this->element->getCaptcha()->getOption('data-tabindex');
        }
        if ($this->element->getCaptcha()->getOption('data-expired-callback') !== null) {
            $attributes['data-expired-callback'] = $this->element->getCaptcha()->getOption('data-expired-callback');
        }

        if ($this->element->getCaptcha()->getOption('data-error-callback') !== null) {
            $attributes['data-error-callback'] = $this->element->getCaptcha()->getOption('data-error-callback');
        }
        return $attributes;
    }
}
