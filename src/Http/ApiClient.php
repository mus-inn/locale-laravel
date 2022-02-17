<?php

namespace Localizy\LocalizyLaravel\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ApiClient
{
    private Client $client;

    public function __construct(string $baseUrl, string $key)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl . '/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $key,
            ],
        ]);
    }

    public function patch(string $uri): Response
    {
        try {
            return new Response(
                $this->client->patch($uri)
            );
        } catch (ClientException $clientException) {
            return new Response($clientException->getResponse());
        }
    }
}
