<?php

namespace UseLocale\LocaleLaravel\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Locale;
use UseLocale\LocaleLaravel\Read\GetAllTranslationsAction;
use UseLocale\LocaleLaravel\Read\GetTranslationsDtoAction;
use UseLocale\LocaleLaravel\Support\CalculateTotalChangedTranslationsAction;
use UseLocale\LocaleLaravel\Write\DeleteEmptyPhpTranslationsFilesAction;
use UseLocale\LocaleLaravel\Write\WriteTranslationsAction;
use function UseLocale\LocaleLaravel\Support\Utils\array_undot;

class SyncCommand extends Command
{
    public $signature = 'locale:sync {--force : Force the operation to run when in production}
    {--purge : All keys that are not present in the current local branch will be permanently deleted}';

    public $description = 'Download updated translations and upload new keys';

    public function handle(
        Filesystem $filesystem,
        Locale $locale,
        GetAllTranslationsAction $getAllTranslationsAction,
        WriteTranslationsAction $writeTranslationsAction,
        CalculateTotalChangedTranslationsAction $calculateTotalChangedTranslationsAction,
        DeleteEmptyPhpTranslationsFilesAction $deleteEmptyPhpTranslationsFilesAction
    ): int
    {
        $this->info('Checking translation changes...');

        try {
            $translations = ($getAllTranslationsAction)();
            $response = $locale->checkSyncTranslationsAction($translations);
            $this->line("We've found {$response['new_keys']} new keys on your local environment.");

            // Check conflicts
            if (filled($response['conflicts'])) {
                $this->warn("Warning, some keys have been changed on both Locale and your environment:");
                foreach ($response['conflicts'] as $key) {
                    $this->line("- {$key}");
                }
            }

            $confirmationMessage = filled($response['conflicts'])
                ? 'Do you want to proceed and overwrite these local values? Otherwise review and resolve the conflict manually.'
                : 'Do you want to proceed uploading new keys and download changes?';

            if (!$this->option('force') and !$this->confirm($confirmationMessage)) {
                return self::SUCCESS;
            }

            // Purge operation
            if ($this->option('purge')) {
                if (filled($response['purgable'])) {
                    $this->warn("Warning, the next keys are not present in your current branch and will be permanently deleted:");
                    foreach ($response['purgable'] as $key) {
                        $this->line("- {$key}");
                    }
                    $confirmationMessage = 'Do you want to proceed and delete permanently these keys?';
                    if (!$this->option('force') and !$this->confirm($confirmationMessage)) {
                        return self::SUCCESS;
                    }
                } else {
                    $this->info('No keys will be deleted');
                }
            }

            // Upload source translations
            $this->info('Uploading translations...');
            $locale->makeUploadRequest($translations, (bool)$this->option('purge'));

            // Download all translations
            $response = $locale->makeDownloadRequest();

            // Count changed translations
            $totalChangedTranslations = ($calculateTotalChangedTranslationsAction)($response->translations);

            // Write new file translations
            $response->translations->each(
                fn(ApiTranslationsDto $apiTranslationsDto) => ($writeTranslationsAction)($apiTranslationsDto)
            );

            // Delete empty PHP Translations Files
            $deleteEmptyPhpTranslationsFilesAction($response->translations);

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
