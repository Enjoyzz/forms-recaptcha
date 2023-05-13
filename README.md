# forms-recaptcha
addon for enjoys/forms

## Run built-in server for view example
```shell
php -S localhost:8000 -t ./example .route
```

### Usage

```php 
// ...before code
// Optional. Set ID for form (for V2Invisible and V3) 
$form->setAttribute(AttributeFactory::create('id', uniqid()));
// Init reCaptcha
$captcha = new reCaptcha($Psr18_HttpClient, $Psr7RequestFactory, $Psr7StreamFactory);

$captcha->setOptions([
    'type' => V3::class, //V2Invisible, V2, V3
    'publicKey' => '...',
    'privateKey' => '...',
    'submitEl' => 'submit1',
    // more options ...
]);

$form->captcha($captcha);
$form->submit('submit1');
// more code...
```

### Global options
- **privateKey** `string` `required` privateKey
- **publicKey** `string` `required` publicKey
- **language** `string` Language `default: en`
- **type** `string` Type `default: \Enjoys\Forms\Captcha\reCaptcha\Type\V2`

# reCAPTCHA v2

_widgets options_
- **data-theme** `string` `dark|light` Optional. The color theme of the widget. `default: light`
- **data-size** `string` `compact|normal` Optional. The size of the widget. `default: normal`
- **data-tabindex** `string` Optional. The tabindex of the widget and challenge. If other elements in your page use tabindex, it should be set to make user navigation easier. `default: null`
- **data-callback** `string` Optional. The name of your callback function, executed when the user submits a successful response. The g-recaptcha-response token is passed to your callback. `default: null`
- **data-expired-callback** `string` Optional. The name of your callback function, executed when the reCAPTCHA response expires and the user needs to re-verify. `default: null`
- **data-error-callback** `string` Optional. The name of your callback function, executed when reCAPTCHA encounters an error (usually network connectivity) and cannot continue until connectivity is restored. If you specify a function here, you are responsible for informing the user that they should retry. `default: null`

# Invisible reCAPTCHA & reCAPTCHA v3

_general options_
- **submitEl** `string` The element submit name. Also, the submit button ID should not be `submit`
- **The form must have an ID attribute.**
- **type** `\Enjoys\Forms\Captcha\reCaptcha\Type\V2Invisible or V2Invisible::class` for Invisible reCAPTCHCA, `\Enjoys\Forms\Captcha\reCaptcha\Type\V3  or V3::class` for  reCAPTCHA v3

_widgets options_
- **data-badge** `string` `bottomright|bottomleft|inline` Optional. Reposition the reCAPTCHA badge. 'inline' lets you position it with CSS. `default: bottomright`
- **data-size** `string` `invisible` Optional. Used to create an invisible widget bound to a div and programmatically executed. `default: null`
- **data-tabindex** `string` Optional. The tabindex of the widget and challenge. If other elements in your page use tabindex, it should be set to make user navigation easier. `default: null`
- **data-callback** `string` Optional. The name of your callback function, executed when the user submits a successful response. The g-recaptcha-response token is passed to your callback. `default: null`
- **data-expired-callback** `string` Optional. The name of your callback function, executed when the reCAPTCHA response expires and the user needs to re-verify. `default: null`
- **data-error-callback** `string` Optional. The name of your callback function, executed when reCAPTCHA encounters an error (usually network connectivity) and cannot continue until connectivity is restored. If you specify a function here, you are responsible for informing the user that they should retry. `default: null`
