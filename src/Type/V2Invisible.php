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
        $formId = $form->getAttribute('id')?->getValueString();
        if ($formId === null) {
            $formId = uniqid('form');
            $form->setAttribute(AttributeFactory::create('id', $formId));
        }
        $submitElement = $form->getElement('sbmt');
        $submitElement->addAttributes(AttributeFactory::createFromArray([
            'class' => 'g-recaptcha',
            'data-sitekey' => $this->reCaptcha->getPublicKey(),
            'data-action' => 'submit',
            'data-callback' => 'onSubmit',
        ]));
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
