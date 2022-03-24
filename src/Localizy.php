<?php

namespace Localizy\LocalizyLaravel;

use Illuminate\Support\Facades\Http;
use Localizy\LocalizyLaravel\Actions\WriteJsonTranslationsAction;
use Localizy\LocalizyLaravel\Actions\WritePhpTranslationsAction;
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
     * @param array $translation
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function makeSetupRequest(array $translation): string
    {
        return Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('setup', ['translations' => $translation])
            ->throw()
            ->json('message');
    }

    public function makeDownloadRequest()
    {
        $response = Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->get('download')
            ->throw();

        $sourceLocale = $response->json('source_locale');

        $translations = collect($response->json('translations'))->map(function ($row) {
            return new ApiTranslationsDto(
                $row['locale'],
                $row['jsonData'],
                $row['phpData']
            );
        });

        /** @var ApiTranslationsDto $localizedTranslations */
        foreach ($translations as $localizedTranslations) {
            app(WriteJsonTranslationsAction::class)($localizedTranslations->locale, $localizedTranslations->jsonData);
            app(WritePhpTranslationsAction::class)($localizedTranslations->locale, $localizedTranslations->phpData, $sourceLocale);
        }
    }
}
