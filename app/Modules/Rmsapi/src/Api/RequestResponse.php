<?php

namespace App\Modules\Rmsapi\src\Api;

use Psr\Http\Message\ResponseInterface;

class RequestResponse
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $response_content;

    /**
     * Api2CartResponse constructor.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        // used casting because PSR-7
        // https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6
        $this->response_content = $response->getBody()->getContents();
    }

    public function getAsJson(): string
    {
        return $this->response_content;
    }

    public function getResponseRaw(): ResponseInterface
    {
        return $this->response;
    }

    public function isSuccess(): bool
    {
        return $this->response->getStatusCode() == 200;
    }

    public function isNotSuccess(): bool
    {
        return ! $this->isSuccess();
    }

    public function asArray(): array
    {
        return json_decode($this->response_content, true);
    }

    public function getResult(): array
    {
        return $this->asArray()['data'];
    }
}
