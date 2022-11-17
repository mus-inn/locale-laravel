<?php

namespace UseLocale\LocaleLaravel\Write;

use Illuminate\Filesystem\Filesystem;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class WriteJsonTranslationsAction
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(string $locale, array $keyValueTranslations): void
    {
        $filePath = lang_path("{$locale}.json");

        $keyValueTranslations = array_filter($keyValueTranslations);

        if (empty($keyValueTranslations)) {
            $this->filesystem->delete($filePath);
            return;
        }
        $fileContent = json_encode($keyValueTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->filesystem->put($filePath, ($fileContent) ?: '');
    }
}
