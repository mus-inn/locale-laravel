<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\RequestException;
use Localizy\LocalizyLaravel\Actions\GetTranslationsDtoAction;
use Localizy\LocalizyLaravel\Actions\WriteTranslationsAction;
use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;
use Localizy\LocalizyLaravel\Localizy;

class SetupCommand extends Command
{
    public $signature = 'localizy:setup';

    public $description = 'My command';

    public function handle(
        Localizy $localizy,
        Filesystem $filesystem,
        GetTranslationsDtoAction $getTranslationsDtoAction,
        WriteTranslationsAction $writeTranslationsAction
    ): int {
        if (! $this->confirm('Descripció comanda + confirmació')) {
            return self::SUCCESS;
        }

        $translations = [];
        foreach ($this->getAllLocales($filesystem) as $locale) {
            $translations[] = ($getTranslationsDtoAction)($locale);
        }

        try {
            $message = $localizy->makeSetupRequest($translations);
            $this->info($message);

            $response = $localizy->makeDownloadRequest();

            /** @var ApiTranslationsDto $translationDto */
            foreach ($response->translations as $translationDto) {
                ($writeTranslationsAction)($translationDto, $response->source_locale);
            }

            $localizy->touchTimestamp();

            $this->info($response->message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));

            return self::FAILURE;
        }
    }

    private function getAllLocales(Filesystem $filesystem): array
    {
        $jsonLocales = collect(
            $filesystem->glob(lang_path('*.json'))
        )->map(fn (string $path) => $filesystem->name($path));

        $phpLocales = collect(
            $filesystem->directories(lang_path())
        )->map(fn ($path) => $filesystem->name($path));

        return $phpLocales->merge($jsonLocales)->unique()->toArray();
    }
}
