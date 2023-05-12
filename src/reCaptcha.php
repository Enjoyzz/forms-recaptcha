<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha;

use Enjoys\Forms\Captcha\reCaptcha\Type\V2;
use Enjoys\Forms\Element;
use Enjoys\Forms\Interfaces\CaptchaInterface;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Forms\Traits\Request;
use GuzzleHttp\Client;

class reCaptcha implements CaptchaInterface
{
    use Request;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private string $privateKey = 'secret_key';
    private string $publicKey = 'site_key';

    /**
     * @var class-string<TypeInterface>
     */
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

    private string $language = 'en';

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function setOption(string $key, mixed $value): self
    {
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            $this->$method($value);
            return $this;
        }

        $this->options[$key] = $value;
        return $this;
    }

     public function getOption(string $key, mixed $defaults = null)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $defaults;
    }

    /**
     * @param array<string, mixed> $options
     * @return $this
     */
    public function setOptions(array $options = []): self
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
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
        return (new $this->type($element))->render();
    }

    public function validate(Ruleable $element): bool
    {
        $client = $this->getOption('httpClient', $this->getGuzzleClient());

        $data = [
            'secret' => $this->getPrivateKey(),
            'response' => $this->getRequest()->getParsedBody()['g-recaptcha-response']
                ?? $this->getRequest()->getQueryParams()['g-recaptcha-response'] ?? null
        ];

        $response = $client->request('POST', self::VERIFY_URL, [
            'form_params' => $data
        ]);

        $responseBody = \json_decode($response->getBody()->getContents());


        if ($responseBody->success === false) {
            $errors = [];
            foreach ($responseBody->{'error-codes'} as $error) {
                $errors[] = $this->getErrorCode($error);
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
        $this->language = \strtolower($lang);
    }

    public function getLanguage(): string
    {
        return $this->language;
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

    /**
     * @return string[]
     */
    public function getErrorCodes(): array
    {
        $file_language = __DIR__ . '/lang/' . $this->getLanguage() . '.php';

        if (file_exists($file_language)) {
            $this->errorCodes = include $file_language;
        }
        return $this->errorCodes;
    }

    public function getErrorCode(string $code): string
    {
        $errorCodes = $this->getErrorCodes();
        return $errorCodes[$code] ?? '';
    }
}
