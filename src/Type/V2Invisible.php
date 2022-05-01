<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha\Type;

use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Elements\Captcha;

class V2Invisible
{
    public function __construct(private reCaptcha $reCaptcha, private Captcha $element)
    {
    }

    public function __invoke(): string
    {
        $form = $this->element->getForm();

        if (null === $formId = $form->getAttribute('id')?->getValueString()) {
            throw new \InvalidArgumentException('Set attribute form id');
        }


        $submitElement = $form->getElement($this->reCaptcha->getOption('submitEl', 'submit'));


        if ($submitElement === null) {
            throw new \InvalidArgumentException('Set correctly submit element name. Option is `submitEl`');
        }

        if ($submitElement->getAttribute('id')->getValueString() === 'submit') {
            throw new \InvalidArgumentException(
                'The submit button ID should not be `submit`, please set a different id for the submit button'
            );
        }

        $submitElement->addAttributes(
            AttributeFactory::createFromArray([
                'class' => 'g-recaptcha',
                'data-sitekey' => $this->reCaptcha->getPublicKey(),
                'data-action' => 'submit',
                'data-callback' => 'onSubmit',
            ])
        );

        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
 <script>
   function onSubmit(token) {
     document.getElementById("%s").submit();
   }
 </script>
HTML,
            $formId
        );
    }
}
