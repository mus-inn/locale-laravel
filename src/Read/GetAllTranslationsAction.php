<?php

namespace UseLocale\LocaleLaravel\Read;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class GetAllTranslationsAction
{
    private GetTranslationsDtoAction $getTranslationsDtoAction;
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem, GetTranslationsDtoAction $getTranslationsDtoAction)
    {
        $this->getTranslationsDtoAction = $getTranslationsDtoAction;
        $this->filesystem = $filesystem;
    }

    /**
     * @return array ApiTranslationsDto
     */
    public function __invoke(): array
    {
        return $this->getAllLocales()
            ->map(fn (string $locale) => ($this->getTranslationsDtoAction)($locale))
            ->toArray();
    }

    private function getAllLocales(): Collection
    {
        $jsonLocales = collect(
            $this->filesystem->glob(lang_path('*.json'))
        )->map(fn (string $path) => $this->filesystem->name($path));

        $phpLocales = collect(
            $this->filesystem->directories(lang_path())
        )->map(fn ($path) => $this->filesystem->name($path));

        return $phpLocales->merge($jsonLocales)->unique();
    }
}
