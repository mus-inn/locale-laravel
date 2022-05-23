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
        if (empty($keyValueTranslations)) {
            return;
        }
        $filePath = lang_path("{$locale}.json");
        $fileContent = json_encode($keyValueTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->filesystem->put($filePath, ($fileContent) ?: '');
    }
}
