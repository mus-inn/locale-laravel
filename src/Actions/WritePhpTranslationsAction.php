<?php

namespace Localizy\LocalizyLaravel\Actions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class WritePhpTranslationsAction
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(string $locale, array $keyValueTranslations, string $sourceLocale): void
    {
        collect($keyValueTranslations)
            ->groupBy(
            // Group by translations by file path
                function (string $translation, string $key) {
                    return explode('.', $key)[0];
                },
                true
            )
            ->map(function (Collection $values, string $filePath) {
                // Generate multi-dimensional arrays
                return Arr::undot($values)[$filePath];
            })
            ->each(function (array $fileTranslations, string $filePath) use ($sourceLocale, $locale) {
                $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                $relativeDirectory = pathinfo($filePath, PATHINFO_DIRNAME);

                // Ensure Directory Exists
                $absoluteDirectory = lang_path("{$locale}/{$relativeDirectory}");
                $this->filesystem->ensureDirectoryExists($absoluteDirectory);

                // Generate file content
                $fileContent = "<?php" . PHP_EOL . PHP_EOL . "return " . $this->prettyVarExport($fileTranslations) . ';' . PHP_EOL;
                if ($locale !== $sourceLocale) {
                    $fileContent = $this->appendWarningComment($fileContent);
                }

                // Write file
                $absoluteFilePath = "{$absoluteDirectory}/{$fileName}.php";
                $this->filesystem->put($absoluteFilePath, $fileContent);
            });
    }

    private function prettyVarExport(array $expression): string
    {
        $output = var_export($expression, true);
        $output = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $output);
        $array = preg_split("/\r\n|\n|\r/", ($output) ?: '');
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], ($array) ?: []);
        $array[0] = '[';
        return join(PHP_EOL, array_filter($array));
    }

    private function appendWarningComment(string $fileContent): string
    {
        $comment = "
    /*
    |--------------------------------------------------------------------------
    | Warning
    |--------------------------------------------------------------------------
    |
    | This file was created automatically and
    | should not be modified manually.
    | Changes will be lost.
    |
    */";

        return str_replace('return [', 'return [' . PHP_EOL . $comment . PHP_EOL, $fileContent);
    }
}
