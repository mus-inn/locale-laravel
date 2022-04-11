<?php

namespace Localizy\LocalizyLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Localizy\LocalizyLaravel\Actions\DiffTranslationsCountAction;
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
        WriteTranslationsAction $writeTranslationsAction,
        DiffTranslationsCountAction $diffTranslationsCountAction
    ): int {
        if (! $this->confirm('Descripció comanda + confirmació')) {
            return self::SUCCESS;
        }

        try {
            // Check new keys & display info
            $sourceLocale = $localizy->fetchSourceLocale();
            $translations = ($getTranslationsDtoAction)($sourceLocale);
            $response = $localizy->checkNewTranslations($translations);

            if ($response['new_keys'] > 0) {
                $this->line("You are about to upload {$response['new_keys']} keys");
            }

            // Check conflicts
            if (filled($response['conflicts'])) {
                $this->warn("Warning, some source keys have been changed.");
                foreach ($response['conflicts'] as $key) {
                    $this->line("- {$key}");
                }
            }

            $confirmationMessage = filled($response['conflicts'])
                ? 'Do you want to proceed and overwrite these keys?'
                : 'Do you want to proceed?';

            if (! $this->confirm($confirmationMessage)) {
                return self::SUCCESS;
            }

            $localizy->touchTimestamp();

            $localizy->makeUploadRequest($translations);

            $response = $localizy->makeDownloadRequest();

            // Write translations files and count changes
            $changedTranslations = 0;

            /** @var ApiTranslationsDto $translationDto */
            foreach ($response->translations as $translationDto) {
                $currentTranslationsDto = ($getTranslationsDtoAction)($translationDto->locale);
                $changedTranslations += ($diffTranslationsCountAction)($currentTranslationsDto, $translationDto);
                ($writeTranslationsAction)($translationDto, $response->source_locale);
            }

            $this->info('Successfully synced');

            $this->info("{$changedTranslations} translations modified");

            $this->line($response->message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));

            return self::FAILURE;
        }
    }
}
