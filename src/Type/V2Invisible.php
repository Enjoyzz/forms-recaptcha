<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha\Type;

use Enjoys\Forms\AttributeFactory;

class V2Invisible extends AbstractType
{
    public function render(): string
    {
        $form = $this->getElement()->getForm();

        if (null === $formAttributeId = $form->getAttribute('id')?->getValueString()) {
            throw new \InvalidArgumentException('Set attribute form id');
        }


        $submitElement = $form->getElement($this->getElement()->getCaptcha()->getOption('submitEl', 'submit'));

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
            $this->getElement()->getCaptcha()->getLanguage(),
            $formAttributeId
        );
    }


    protected function getAttributes(): array
    {
        return array_filter([
            'class' => 'g-recaptcha',
            'data-sitekey' => $this->getElement()->getCaptcha()->getPublicKey(),
            'data-callback' => $this->getElement()->getCaptcha()->getOption('data-callback', 'onSubmit'),
            'data-badge' => $this->getElement()->getCaptcha()->getOption('data-badge'),
            'data-size' => $this->getElement()->getCaptcha()->getOption('data-size'),
            'data-tabindex' => $this->getElement()->getCaptcha()->getOption('data-tabindex'),
            'data-expired-callback' => $this->getElement()->getCaptcha()->getOption('data-expired-callback'),
            'data-error-callback' => $this->getElement()->getCaptcha()->getOption('data-error-callback'),
        ], function ($value) {
            return $value !== null;
        });
    }
}
