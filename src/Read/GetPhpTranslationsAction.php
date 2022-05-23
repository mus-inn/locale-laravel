<?php

namespace UseLocale\LocaleLaravel\Read;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\SplFileInfo;
use function UseLocale\LocaleLaravel\Support\Utils\array_dot;

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

        if (! $this->filesystem->exists($localePath)) {
            return [];
        }

        /** @var (callable(\Symfony\Component\Finder\SplFileInfo): array) $transformDirectoriesToKeyCallback */
        $transformDirectoriesToKeyCallback = function (SplFileInfo $file) use ($locale) {
            // Generate group key
            $group = collect([
                $file->getRelativePath(), $file->getFilenameWithoutExtension(),
            ])->filter()->implode(DIRECTORY_SEPARATOR);

            // Convert array  file content to dot notation
            $phpTranslations = array_dot([
                $group => $this->translator->getLoader()->load($locale, $group),
            ]);

            return array_filter($phpTranslations);
        };

        return collect($this->filesystem->allFiles($localePath))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->mapWithKeys($transformDirectoriesToKeyCallback)
            ->toArray();
    }
}
