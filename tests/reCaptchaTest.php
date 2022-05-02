<?php

namespace Tests\Enjoys\Forms\Captcha\reCaptcha;

use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Elements\Captcha;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class reCaptchaTest extends TestCase
{
    private function getHttpClient($contentType, $responseBody, $extraHeaders = [])
    {
        $extraHeaders['Content-Type'] = $contentType;

        $response = $this->getMockBuilder(Response::class)->setMethods(['hasHeader', 'getHeader', 'getBody'])->getMock();
        $response->expects($this->any())->method('hasHeader')->will($this->returnCallback(function ($headerName) use ($extraHeaders) {
                    return \array_key_exists($headerName, $extraHeaders);
        }));
        $response->expects($this->any())->method('getHeader')->will($this->returnCallback(function ($headerName) use ($extraHeaders) {
                    return [$extraHeaders[$headerName]];
        }));

        $stream = $this->getMockBuilder(Stream::class)->disableOriginalConstructor()->setMethods(['__toString', 'getContents'])->getMock();
        $stream->expects($this->any())->method('__toString')->willReturn($responseBody);
        $response->expects($this->any())->method('getBody')->willReturn($stream);
        $stream->expects($this->any())->method('getContents')->willReturn($responseBody);

        $http = $this->getMockBuilder(Client::class)->setMethods(['request'])->getMock();
        $http->expects($this->any())->method('request')->will($this->returnCallback(function ($method, $address, array $options) use ($response) {
                    $this->lastRequestOptions = $options;
                    Assert::keyExists($options, 'form_params');
                    Assert::allKeyExists($options, 'response');
                    Assert::allKeyExists($options, 'secret');
                    return $response;
        }));

        return $http;
    }

    private function toOneString($multistring)
    {
        return preg_replace('/\s+/', ' ', $multistring);
    }

    public function testInit()
    {
        $recaptcha = new reCaptcha();
        $captcha = new Captcha($recaptcha);
        $this->assertInstanceOf(Captcha::class, $captcha);
    }

    public function testAddRule()
    {

        $recaptcha = new reCaptcha();
        $captcha = new Captcha($recaptcha);
        $captcha->prepare();
        $this->assertCount(1, $captcha->getRules());
    }

    public function testRender()
    {

        $recaptcha = new reCaptcha();
        $captcha = new Captcha($recaptcha);
        $this->assertStringContainsString(
            '<script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script> <div class="g-recaptcha" data-sitekey="site_key"></div>',
            $this->toOneString($captcha->baseHtml())
        );
    }

    public function testValidateSuccess()
    {

        $responseBody = \json_encode([
            'success' => true,
        ]);

        $captcha = new reCaptcha();
        $captcha->setOptions([
            'httpClient' => $this->getHttpClient('text/plain', $responseBody)
        ]);

        $captcha_element = new Captcha($captcha);
        $captcha_element->prepare();

        $this->assertTrue($captcha_element->validate());
    }

    public function testValidateFalse()
    {
        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
            [
                0 => 'missing-input-response',
            ],
        ]);
        $recaptcha = new reCaptcha();
        $recaptcha->setOptions([
            'httpClient' => $this->getHttpClient('text/plain', $responseBody)
        ]);


        $captcha = new Captcha($recaptcha);
        $captcha->prepare();

        $this->assertFalse($captcha->validate());
        $this->assertEquals('The response parameter is missing.', $captcha->getRuleErrorMessage());
    }

    public function testValidateFalseRender()
    {

        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
            [
                0 => 'missing-input-response',
                1 => 'invalid-input-secret'
            ],
        ]);
        $recaptcha = new reCaptcha();
        $recaptcha->setOptions([
            'httpClient' => $this->getHttpClient('text/plain', $responseBody)
        ]);
        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals('The response parameter is missing., The secret parameter is invalid or malformed.', $captcha->getRuleErrorMessage());
    }

    public function testValidateFalseRenderWidthSetLanguageViaOptions()
    {
        $recaptcha = new reCaptcha();
        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
            [
                0 => 'missing-input-response',
                1 => 'invalid-input-secret'
            ],
        ]);

        $recaptcha->setOptions([
            'httpClient' => $this->getHttpClient('text/plain', $responseBody),
            'language' => 'ru'
        ]);

        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals('Параметр ответа отсутствует., Секретный параметр является недопустимым или искаженным.', $captcha->getRuleErrorMessage());
    }

    public function testValidateFalseRenderWidthSetLanguageViaMethod()
    {
        $recaptcha = new reCaptcha();
        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
                [
                    0 => 'invalid-input-secret'
                ],
        ]);

        $recaptcha->setOptions([
            'httpClient' => $this->getHttpClient('text/plain', $responseBody),
        ]);

        $recaptcha->setLanguage('RU');

        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals('Секретный параметр является недопустимым или искаженным.', $captcha->getRuleErrorMessage());
    }
}
