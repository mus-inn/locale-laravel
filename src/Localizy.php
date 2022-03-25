<?php

namespace Localizy\LocalizyLaravel;

use Illuminate\Support\Facades\Http;
use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;

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
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function fetchSourceLocale(): string
    {
        return Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->get('source-locale')
            ->throw()
            ->json('source_locale');
    }

    /**
     * @param array $translations
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function makeSetupRequest(array $translations): string
    {
        return Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('setup', ['translations' => $translations])
            ->throw()
            ->json('message');
    }

    public function fetchChanges(ApiTranslationsDto $translations): array
    {
        $aux = Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('changes', ['translations' => $translations])
            ->throw();
        dump($aux->json());
        return $aux->json();
    }

    /**
     * @param ApiTranslationsDto $translations
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function makeUploadRequest(ApiTranslationsDto $translations): void
    {
        Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('upload', ['translations' => $translations])
            ->throw();
    }

    /**
     * @return object
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function makeDownloadRequest(): object
    {
        $response = Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->get('download')
            ->throw();

        $translations = collect($response->json('translations'))
            ->map(function (array $row) {
                return new ApiTranslationsDto(
                    $row['locale'],
                    $row['jsonData'],
                    $row['phpData']
                );
            });

        return (object)[
            'message' => $response->json('message'),
            'source_locale' => $response->json('source_locale'),
            'translations' => $translations,
        ];
    }
}
