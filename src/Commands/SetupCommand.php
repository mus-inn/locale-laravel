<?php

namespace UseLocale\LocaleLaravel\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Locale;
use UseLocale\LocaleLaravel\Read\GetAllTranslationsAction;
use UseLocale\LocaleLaravel\Write\WriteTranslationsAction;

class SetupCommand extends Command
{
    public $signature = 'locale:setup';

    public $description = 'Setup your Locale project and upload your localized files';

    public function handle(
        Locale $locale,
        GetAllTranslationsAction $getAllTranslationsAction,
        WriteTranslationsAction $writeTranslationsAction
    ): int {
        if (! $this->confirm('Starting to upload your localized files' . PHP_EOL . "After this initial setup, your localization files will be formatted without changing its content. Do you want to proceed?")) {
            return self::SUCCESS;
        }

        $this->info('Uploading translations...');

        // Get all translations from files
        $translations = ($getAllTranslationsAction)();

        try {
            // Upload translations
            $message = $locale->makeSetupRequest($translations);
            $this->info($message);

            // Download translations
            $response = $locale->makeDownloadRequest();

            // Write new file translations
            $response->translations->each(
                fn (ApiTranslationsDto $apiTranslationsDto) => ($writeTranslationsAction)($apiTranslationsDto, $response->source_locale)
            );

            // Display information messages
            $this->info($response->message);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error($exception->response->json('message'));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        return self::FAILURE;
    }
}
