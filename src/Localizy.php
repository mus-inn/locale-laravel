<?php

namespace Localizy\LocalizyLaravel;

use Localizy\LocalizyLaravel\Http\ApiClient;
use Localizy\LocalizyLaravel\Http\Response;

class Localizy
{
    protected ApiClient $apiClient;

    public function __construct(string $baseUrl, string $key)
    {
        $this->apiClient = new ApiClient($baseUrl, $key);
    }

    public function makeSetupRequest(): Response
    {
        return $this->apiClient->patch('setup');
    }
}
