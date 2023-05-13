<?php

namespace Tests\Enjoys\Forms\Captcha\reCaptcha;

use Enjoys\Forms\Captcha\reCaptcha\reCaptcha;
use Enjoys\Forms\Elements\Captcha;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class reCaptchaTest extends TestCase
{
    private function getHttpClient($contentType, $responseBody, $extraHeaders = [])
    {
        $extraHeaders['Content-Type'] = $contentType;

        $response = $this->getMockBuilder(Response::class)->setMethods(['hasHeader', 'getHeader', 'getBody'])->getMock(
        );
        $response->expects($this->any())->method('hasHeader')->will(
            $this->returnCallback(function ($headerName) use ($extraHeaders) {
                return \array_key_exists($headerName, $extraHeaders);
            })
        );
        $response->expects($this->any())->method('getHeader')->will(
            $this->returnCallback(function ($headerName) use ($extraHeaders) {
                return [$extraHeaders[$headerName]];
            })
        );

        $stream = $this->getMockBuilder(Stream::class)->disableOriginalConstructor()->setMethods(
            ['__toString', 'getContents']
        )->getMock();
        $stream->expects($this->any())->method('__toString')->willReturn($responseBody);
        $response->expects($this->any())->method('getBody')->willReturn($stream);
        $stream->expects($this->any())->method('getContents')->willReturn($responseBody);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->any())->method('sendRequest')->willReturn(
            $response
        );

        return $client;
    }


    private function toOneString($multistring)
    {
        return preg_replace('/\s+/', ' ', $multistring);
    }

    public function testInit()
    {
        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', ''),
            new HttpFactory(),
            new HttpFactory(),
        );
        $captcha = new Captcha($recaptcha);
        $this->assertInstanceOf(Captcha::class, $captcha);
    }

    public function testAddRule()
    {
        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', ''),
            new HttpFactory(),
            new HttpFactory(),
        );
        $captcha = new Captcha($recaptcha);
        $captcha->prepare();
        $this->assertCount(1, $captcha->getRules());
    }

    public function testRender()
    {
        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', ''),
            new HttpFactory(),
            new HttpFactory(),
        );
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

        $captcha = new reCaptcha(
            $this->getHttpClient('text/plain', $responseBody),
            new HttpFactory(),
            new HttpFactory(),
        );


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
        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', $responseBody),
            new HttpFactory(),
            new HttpFactory(),
        );


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
        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', $responseBody),
            new HttpFactory(),
            new HttpFactory(),
        );

        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals(
            'The response parameter is missing., The secret parameter is invalid or malformed.',
            $captcha->getRuleErrorMessage()
        );
    }

    public function testValidateFalseRenderWidthSetLanguageViaOptions()
    {
        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
                [
                    0 => 'missing-input-response',
                    1 => 'invalid-input-secret'
                ],
        ]);

        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', $responseBody),
            new HttpFactory(),
            new HttpFactory(),
        );


        $recaptcha->setOptions([
            'language' => 'ru'
        ]);

        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals(
            'Параметр ответа отсутствует., Секретный параметр является недопустимым или искаженным.',
            $captcha->getRuleErrorMessage()
        );
    }

    public function testValidateFalseRenderWidthSetLanguageViaMethod()
    {
        $responseBody = \json_encode([
            'success' => false,
            'error-codes' =>
                [
                    0 => 'invalid-input-secret'
                ],
        ]);


        $recaptcha = new reCaptcha(
            $this->getHttpClient('text/plain', $responseBody),
            new HttpFactory(),
            new HttpFactory(),
        );

        $recaptcha->setLanguage('RU');

        $captcha = new Captcha($recaptcha);
        $captcha->prepare();


        $captcha->validate();
        $captcha->baseHtml();
        $this->assertEquals(
            'Секретный параметр является недопустимым или искаженным.',
            $captcha->getRuleErrorMessage()
        );
    }
}
