<?php

namespace UseLocale\LocaleLaravel\Write;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use function UseLocale\LocaleLaravel\Support\Utils\array_undot;

class WritePhpTranslationsAction
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(string $locale, array $keyValueTranslations): void
    {
        collect($keyValueTranslations)
            ->filter()
            ->groupBy(
                // Group by translations by file path
                function (string $translation, string $key) {
                    return explode('.', $key)[0];
                },
                true
            )
            ->map(function (Collection $values, string $filePath) {
                // Generate multi-dimensional arrays
                return array_undot($values)[$filePath];
            })
            ->each(function (array $fileTranslations, string $filePath) use ($locale) {
                $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                $relativeDirectory = pathinfo($filePath, PATHINFO_DIRNAME);

                // Ensure Directory Exists
                $absoluteDirectory = lang_path($locale . DIRECTORY_SEPARATOR . $relativeDirectory);
                $absoluteDirectory = rtrim($absoluteDirectory, DIRECTORY_SEPARATOR.'.');
                $this->filesystem->ensureDirectoryExists($absoluteDirectory);

                // Generate file content
                $fileContent = "<?php" . PHP_EOL . PHP_EOL . "return " . $this->phpVarExport($fileTranslations) . ';' . PHP_EOL;

                // Write file
                $absoluteFilePath = $absoluteDirectory . DIRECTORY_SEPARATOR . "{$fileName}.php";
                $this->filesystem->put($absoluteFilePath, $fileContent);
            })
            ->map(function (array $fileTranslations, string $filePath) use ($locale) {
                $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                $relativeDirectory = pathinfo($filePath, PATHINFO_DIRNAME);

                $absoluteDirectory = lang_path($locale . DIRECTORY_SEPARATOR . $relativeDirectory);
                $absoluteDirectory = rtrim($absoluteDirectory, DIRECTORY_SEPARATOR.'.');

                return $absoluteDirectory . DIRECTORY_SEPARATOR . "{$fileName}.php";
            });
    }

    private function phpVarExport($expression, string $indent = ''): string
    {
        $tab = '    ';

        if (is_array($expression)) {
            $lines = [];
            foreach ($expression as $key => $value) {
                $key = var_export($key, true);
                $value = $this->phpVarExport($value, $indent . $tab);
                $lines[] = "{$indent}{$tab}{$key} => {$value}";
            }

            return '[' . PHP_EOL . implode(',' . PHP_EOL, $lines) . PHP_EOL . $indent . ']';
        }

        return var_export($expression, true);
    }
}
