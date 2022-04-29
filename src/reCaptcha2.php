<?php

declare(strict_types=1);

namespace Enjoys\Forms\Captcha\reCaptcha2;

use Enjoys\Forms\Captcha\CaptchaBase;
use Enjoys\Forms\Captcha\CaptchaInterface;
use Enjoys\Forms\Element;
use Enjoys\Forms\Interfaces\Ruleable;
use Enjoys\Forms\Traits\Request;
use Enjoys\Traits\Options;
use GuzzleHttp\Client;

class reCaptcha2 implements CaptchaInterface
{
    use Options;
    use Request;

    private string $privateKey = '6LdUGNEZAAAAAPPz685RwftPySFeCLbV1xYJJjsk'; //localhost
    private string $publicKey = '6LdUGNEZAAAAANA5cPI_pCmOqbq-6_srRkcGOwRy'; //localhost
    private string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    private array $errorCodes;
    private string $name = 'recaptcha2';
    private ?string $ruleMessage = null;


    public function __construct(array $options = [])
    {
        $this->errorCodes = include __DIR__ . '/lang/en.php';
        $this->setOptions($options);
    }

    public function getName(): string
    {
        return $this->name;
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
        return sprintf(
            <<<HTML
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="g-recaptcha" data-sitekey="%s"></div>
HTML,
            $this->getOption('publickey', $this->getOption('publickey', $this->publicKey))
        );
    }

    public function validate(Ruleable $element): bool
    {
        $client = $this->getOption('httpClient', $this->getGuzzleClient());

        $data = [
            'secret' => $this->getOption('privatekey', $this->getOption('privatekey', $this->privateKey)),
            'response' => $this->getRequest()->getPostData(
                'g-recaptcha-response',
                $this->getRequest()->getQueryData('g-recaptcha-response')
            )
        ];

        $response = $client->request('POST', $this->getOption('verify_url', $this->verifyUrl), [
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
}
