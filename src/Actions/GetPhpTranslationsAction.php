<?php

namespace Localizy\LocalizyLaravel\Actions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\SplFileInfo;

class GetPhpTranslationsAction
{
    private Filesystem $filesystem;
    private Translator $translator;

    public function __construct(Filesystem $filesystem, Translator $translator)
    {
        $this->filesystem = $filesystem;
        $this->translator = $translator;
    }

    public function __invoke(string $locale): array
    {
        $localePath = lang_path($locale);

        if (!$this->filesystem->exists($localePath)) {
            return [];
        }

        return collect($this->filesystem->allFiles($localePath))
            ->filter(fn($file) => $file->getExtension() === 'php')
            ->mapWithKeys(function (SplFileInfo $file) use ($locale) {

                // Generate group key
                $group = collect([
                    $file->getRelativePath(), $file->getFilenameWithoutExtension(),
                ])->filter()->implode('/');

                // Convert array  file content to dot notation
                $phpTranslations = Arr::dot([
                    $group => $this->translator->getLoader()->load($locale, $group),
                ]);

                return collect($phpTranslations)->filter();
            })
            ->toArray();
    }
}
