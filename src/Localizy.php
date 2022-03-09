<?php

namespace Localizy\LocalizyLaravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Localizy
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $key)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $key;
    }

    /**
     * @param array $translation
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function makeSetupRequest(array $translation): void
    {
        Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('setup', ['translations' => $translation])
            ->throw();
    }
}
