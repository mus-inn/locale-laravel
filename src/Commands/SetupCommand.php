<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;
use Localizy\LocalizyLaravel\Localizy;
use Symfony\Component\Finder\SplFileInfo;

class SetupCommand extends Command
{
    public $signature = 'localizy:setup';

    public $description = 'My command';

    public function handle(Filesystem $filesystem, Translator $translator, Localizy $localizy): int
    {
        if (!$this->confirm('Descripció comanda + confirmació')) {
            return self::SUCCESS;
        }

        $translations = [];
        foreach ($this->getAllLocales($filesystem) as $locale) {
            $translations[] = new ApiTranslationsDto(
                $locale,
                $this->getJsonTranslations($filesystem, $locale),
                $this->getPhpTranslations($filesystem, $translator, $locale)
            );
        }

        try {
            $message = $localizy->makeSetupRequest($translations);
            $this->info($message);

            $message = $localizy->makeDownloadRequest();
            $this->info($message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));

            return self::FAILURE;
        }
    }

    private function getJsonTranslations(Filesystem $filesystem, string $locale): array
    {
        $jsonPath = lang_path() . "/{$locale}.json";

        if (!$filesystem->exists($jsonPath)) {
            return [];
        }

        return json_decode($filesystem->get($jsonPath), true);
    }

    private function getPhpTranslations(Filesystem $filesystem, Translator $translator, string $locale): array
    {
        $localePath = lang_path($locale);

        if (!$filesystem->exists($localePath)) {
            return [];
        }

        return collect($filesystem->allFiles($localePath))
            ->filter(fn($file) => $file->getExtension() === 'php')
            ->mapWithKeys(function (SplFileInfo $file) use ($translator, $locale) {

                // Generate group key
                $group = collect([
                    $file->getRelativePath(), $file->getFilenameWithoutExtension(),
                ])->filter()->implode('/');

                // Convert array  file content to dot notation
                $phpTranslations = Arr::dot([
                    $group => $translator->getLoader()->load($locale, $group),
                ]);

                return collect($phpTranslations)->filter();
            })
            ->toArray();
    }

    private function getAllLocales(Filesystem $filesystem): array
    {
        $jsonLocales = collect(
            $filesystem->glob(lang_path('*.json'))
        )->map(fn(string $path) => $filesystem->name($path));

        $phpLocales = collect(
            $filesystem->directories(lang_path())
        )->map(fn($path) => $filesystem->name($path));

        return $phpLocales->merge($jsonLocales)->unique()->toArray();
    }
}
