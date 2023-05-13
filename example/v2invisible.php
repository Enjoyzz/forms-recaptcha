<?php

declare(strict_types=1);

use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Captcha\reCaptcha\Type\V2Invisible;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;

require __DIR__ . '/../vendor/autoload.php';


try {
    $form = new Form();
    $form->addAttribute(AttributeFactory::create('id', 'form_id'));

    $form->text('text');

    $captcha = new reCaptcha(
        httpClient: new \GuzzleHttp\Client(),
        requestFactory: new \GuzzleHttp\Psr7\HttpFactory(),
        streamFactory: new \GuzzleHttp\Psr7\HttpFactory()
    );

    $captcha->setOptions([
        'type' => V2Invisible::class,
//        'language' => 'ru',
        'submitEl' => 'submit1',
        'publicKey' => '6LdgYbYfAAAAAOb-So1MDXx1PSSshPGI8hnoKNV_',
        'privateKey' => '6LdgYbYfAAAAAJEUvegXGVR9NDmJNsOJqsXOA3vI',
    ]);

    $form->captcha($captcha);
    $form->submit('submit1');
    if ($form->isSubmitted()) {
        dump($_REQUEST);
    }
    $renderer = new HtmlRenderer($form);
    echo include __DIR__ . '/.assets.php';
    echo sprintf('<div class="container-fluid">%s</div>', $renderer->output());
} catch (Exception|Error $e) {
    echo 'Error: ' . $e->getMessage();
}
