<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha;

use Enjoys\Forms\Captcha\reCaptcha\Type\V2;
use Enjoys\Forms\Element;
use Enjoys\Forms\Interfaces\CaptchaInterface;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Forms\Traits\Request;
use Enjoys\Traits\Options;
use GuzzleHttp\Client;

class reCaptcha implements CaptchaInterface
{
    use Options;
    use Request;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private string $privateKey = 'secret_key';
    private string $publicKey = 'site_key';

    private string $type = V2::class;
    private array $errorCodes = [
        'missing-input-secret' => 'The secret parameter is missing.',
        'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
        'bad-request' => 'The request is invalid or malformed.',
        'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously.',
    ];

    private ?string $ruleMessage = null;


    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function getName(): string
    {
        return 'g-recaptcha';
    }

    public function getRuleMessage(): ?string
    {
        return $this->ruleMessage;
    }

    public function setRuleMessage(?string $message = null): void
    {
        $this->ruleMessage = $message;
    }

    public function renderHtml(Element $element): string
    {
        return (new $this->type($this, $element))();
    }

    public function validate(Ruleable $element): bool
    {
        $client = $this->getOption('httpClient', $this->getGuzzleClient());

        $data = [
            'secret' => $this->getPrivateKey(),
            'response' => $this->getRequest()->getPostData(
                'g-recaptcha-response',
                $this->getRequest()->getQueryData('g-recaptcha-response')
            )
        ];

        $response = $client->request('POST', self::VERIFY_URL, [
            'form_params' => $data
        ]);

        $responseBody = \json_decode($response->getBody()->getContents());


        if ($responseBody->success === false) {
            $errors = [];
            foreach ($responseBody->{'error-codes'} as $error) {
                $errors[] = $this->errorCodes[$error];
            }
            /** @psalm-suppress UndefinedMethod */
            $element->setRuleError(implode(', ', $errors));
            return false;
        }
        return true;
    }

    /**
     * Used across setOption()
     * @param string $lang
     * @return void
     */
    public function setLanguage(string $lang): void
    {
        $file_language = __DIR__ . '/lang/' . \strtolower($lang) . '.php';

        if (file_exists($file_language)) {
            $this->errorCodes = include $file_language;
        }
    }

    /**
     *
     * @return Client
     */
    private function getGuzzleClient(): Client
    {
        return new Client();
    }


    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }


    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }


    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
