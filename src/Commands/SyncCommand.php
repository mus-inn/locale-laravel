<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Localizy\LocalizyLaravel\Actions\GetTranslationsDtoAction;
use Localizy\LocalizyLaravel\Actions\WriteTranslationsAction;
use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;
use Localizy\LocalizyLaravel\Localizy;

class SyncCommand extends Command
{
    public $signature = 'localizy:sync';

    public $description = 'My command';

    public function handle(
        Localizy $localizy,
        GetTranslationsDtoAction $getTranslationsDtoAction,
        WriteTranslationsAction $writeTranslationsAction
    ): int {
        if (! $this->confirm('Descripció comanda + confirmació')) {
            return self::SUCCESS;
        }

        try {
            $sourceLocale = $localizy->fetchSourceLocale();

            $translations = ($getTranslationsDtoAction)($sourceLocale);

            $response = $localizy->fetchChanges($translations);

            // Show changes and conflicts

            $localizy->makeUploadRequest($translations);

            $response = $localizy->makeDownloadRequest();

            /** @var ApiTranslationsDto $translationDto */
            foreach ($response->translations as $translationDto) {
                ($writeTranslationsAction)($translationDto, $response->source_locale);
            }

            $this->line($response->message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));

            return self::FAILURE;
        }
    }
}
