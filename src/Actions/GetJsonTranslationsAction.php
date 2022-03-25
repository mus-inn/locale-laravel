<?php

namespace Localizy\LocalizyLaravel\Actions;

use Illuminate\Filesystem\Filesystem;

class GetJsonTranslationsAction
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(string $locale): array
    {
        $jsonPath = lang_path("{$locale}.json");

        if (!$this->filesystem->exists($jsonPath)) {
            return [];
        }

        return json_decode($this->filesystem->get($jsonPath), true);
    }
}
