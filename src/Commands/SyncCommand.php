<?php

namespace UseLocale\LocaleLaravel\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Locale;
use UseLocale\LocaleLaravel\Read\GetTranslationsDtoAction;
use UseLocale\LocaleLaravel\Support\CalculateTotalChangedTranslationsAction;
use UseLocale\LocaleLaravel\Write\WriteTranslationsAction;

class SyncCommand extends Command
{
    public $signature = 'locale:sync';

    public $description = 'Download updated translations and upload new keys';

    public function handle(
        Locale $locale,
        GetTranslationsDtoAction $getTranslationsDtoAction,
        WriteTranslationsAction $writeTranslationsAction,
        CalculateTotalChangedTranslationsAction $calculateTotalChangedTranslationsAction
    ): int {
        $this->info('Checking translation changes...');

        try {
            // Check new keys & display info
            $sourceLocale = $locale->fetchSourceLocale();
            $translations = ($getTranslationsDtoAction)($sourceLocale);
            $response = $locale->checkNewTranslations($translations);

            $this->line("We've found {$response['new_keys']} new keys on your local environment.");

            // Check conflicts
            if (filled($response['conflicts'])) {
                $this->warn("Warning, some source keys have been changed on both Locale and your environment:");
                foreach ($response['conflicts'] as $key) {
                    $this->line("- {$key}");
                }
            }

            $confirmationMessage = filled($response['conflicts'])
                ? 'Do you want to proceed and overwrite these local values? Otherwise review and resolve the conflict manually.'
                : 'Do you want to proceed uploading new keys and download changes?';

            if (! $this->confirm($confirmationMessage)) {
                return self::SUCCESS;
            }

            // Upload source translations
            $this->info('Uploading translations...');
            $locale->makeUploadRequest($translations);

            // Download all translations
            $response = $locale->makeDownloadRequest();

            // Count changed translations
            $totalChangedTranslations = ($calculateTotalChangedTranslationsAction)($response->translations);

            // Write new file translations
            $response->translations->each(
                fn (ApiTranslationsDto $apiTranslationsDto) => ($writeTranslationsAction)($apiTranslationsDto, $response->source_locale)
            );

            // Display information messages
            $this->info('Successfully synced');
            $this->info("{$totalChangedTranslations} translations modified");
            $this->line($response->message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        return self::FAILURE;
    }
}
