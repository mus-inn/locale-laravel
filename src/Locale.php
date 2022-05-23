<?php

namespace UseLocale\LocaleLaravel;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Support\MetadataManager;

class Locale
{
    protected string $baseUrl;
    protected string $apiKey;
    private MetadataManager $metadataManager;

    public function __construct(Filesystem $filesystem, string $baseUrl, string $key)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $key;
        $this->metadataManager = new MetadataManager($filesystem);
    }

    /**
     * @return string
     * @throws RequestException
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
     * @throws RequestException
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

    /**
     * @param ApiTranslationsDto $translations
     * @return array
     * @throws RequestException
     */
    public function checkNewTranslations(ApiTranslationsDto $translations): array
    {
        return Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('changes', [
                'translations' => $translations,
                'timestamp' => $this->metadataManager->getLastSyncTimestamp(),
                'snapshot' => $this->metadataManager->getSnapshotContent(),
            ])
            ->throw()
            ->json();
    }

    /**
     * @param ApiTranslationsDto $translations
     * @return void
     * @throws RequestException
     */
    public function makeUploadRequest(ApiTranslationsDto $translations): void
    {
        Http::acceptJson()
            ->withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->patch('upload', [
                'translations' => $translations,
                'timestamp' => $this->metadataManager->getLastSyncTimestamp(),
            ])
            ->throw();
    }

    /**
     * @return object
     * @throws RequestException
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

        $sourceLocale = $response->json('source_locale');

        $this->metadataManager->touchTimestamp();

        $this->metadataManager->updateSnapshot(
            $translations->where('locale', $sourceLocale)->first()
        );

        return (object)[
            'message' => $response->json('message'),
            'source_locale' => $sourceLocale,
            'translations' => $translations,
        ];
    }
}
