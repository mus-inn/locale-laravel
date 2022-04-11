<?php

namespace Localizy\LocalizyLaravel;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;

class Localizy
{
    protected string $baseUrl;
    protected string $apiKey;
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem, string $baseUrl, string $key)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $key;
        $this->filesystem = $filesystem;
    }

    private function metaDataFilePath(): string
    {
        return lang_path('.uselocale.com');
    }

    private function getLastSyncTimestamp(): int
    {
        return ($this->filesystem->exists($this->metaDataFilePath()))
            ? $this->filesystem->get($this->metaDataFilePath())
            : 0;
    }

    public function touchTimestamp(): void
    {
        $this->filesystem->put(
            $this->metaDataFilePath(),
            Carbon::now()->timestamp
        );
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
                'timestamp' => $this->getLastSyncTimestamp(),
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
            ->patch('upload', ['translations' => $translations])
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

        return (object)[
            'message' => $response->json('message'),
            'source_locale' => $response->json('source_locale'),
            'translations' => $translations,
        ];
    }
}
