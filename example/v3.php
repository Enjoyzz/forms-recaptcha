<?php

declare(strict_types=1);

use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Captcha\reCaptcha\Type\V3;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;

require __DIR__ . '/../vendor/autoload.php';

try {
    $form = new Form();
    $form->setAttribute(AttributeFactory::create('id', uniqid()));

    $form->text('text');

    $captcha = new reCaptcha(
        httpClient: new \GuzzleHttp\Client(),
        requestFactory: new \GuzzleHttp\Psr7\HttpFactory(),
        streamFactory: new \GuzzleHttp\Psr7\HttpFactory(),
        options: [
            'type' => V3::class,
            'publicKey' => '6LcnkLYfAAAAAPFnJLrwnm_AaCX4ZhJ65iVElS1a',
            'privateKey' => '6LcnkLYfAAAAAK5OiBeiFKwdcI156CaYt0bgo_AW',
            'submitEl' => 'sbmt',
        ]
    );
    $form->captcha($captcha);

    $form->submit('sbmt');
    if ($form->isSubmitted()) {
        dump($_REQUEST);
    }
    $renderer = new HtmlRenderer($form);
    echo include __DIR__ . '/.assets.php';
    echo sprintf('<div class="container-fluid">%s</div>', $renderer->output());
} catch (Exception|Error $e) {
    echo 'Error: ' . $e->getMessage();
}
