<?php

namespace Localizy\LocalizyLaravel\Http;

use Psr\Http\Message\ResponseInterface;

class Response
{
    private int $code;
    private ?string $message;

    public function __construct(ResponseInterface $response)
    {
        $this->code = $response->getStatusCode();
        $content = json_decode($response->getBody()->getContents());
        $this->message = $content->message ?? null;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message ?? 'Undefined error';
    }

    public function hasErrors(): bool
    {
        return ! ($this->code >= 200 and $this->code < 300);
    }
}
