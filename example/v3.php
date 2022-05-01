<?php

declare(strict_types=1);

use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Captcha\reCaptcha\Type\V2Invisible;
use Enjoys\Forms\Form;
use Enjoys\Forms\Renderer\Html\HtmlRenderer;

require __DIR__ . '/../vendor/autoload.php';


$form = new Form();
$form->setAttribute(\Enjoys\Forms\AttributeFactory::create('id', 'test'));
$captcha = new reCaptcha([
    'type' => \Enjoys\Forms\Captcha\reCaptcha\Type\V3::class ,
    'publicKey' => '6LcnkLYfAAAAAPFnJLrwnm_AaCX4ZhJ65iVElS1a',
    'privateKey' => '6LcnkLYfAAAAAK5OiBeiFKwdcI156CaYt0bgo_AW',
]);
$form->captcha($captcha);
$form->text('text');
$form->submit('sbmt');
if ($form->isSubmitted()) {
    dump($_REQUEST);
}
$renderer = new HtmlRenderer($form);
echo include __DIR__ . '/.assets.php';
echo sprintf('<div class="container-fluid">%s</div>', $renderer->output());
