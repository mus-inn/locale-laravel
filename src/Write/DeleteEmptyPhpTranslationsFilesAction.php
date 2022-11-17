<?php

namespace UseLocale\LocaleLaravel\Write;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

class DeleteEmptyPhpTranslationsFilesAction
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(Collection $apiTranslationsDto): void
    {
        $absolutePathPhpFilesWithTranslations = $this->getPhpFilesWithTranslationsAbsolutePaths($apiTranslationsDto);

        /** @var Collection<int, SplFileInfo> $allTranslationsFiles */
        $allTranslationsFiles = collect($this->filesystem->allFiles(lang_path()));

        $allTranslationsFiles
            ->filter(fn(SplFileInfo $file) => $file->getExtension() === 'php')
            // @phpstan-ignore-next-line
            ->reject(fn(SplFileInfo $file) => (Str::startsWith($file->getRealPath(), lang_path('vendor'))))
            // @phpstan-ignore-next-line
            ->reject(fn(SplFileInfo $file) => $absolutePathPhpFilesWithTranslations->contains($file->getRealPath()))
            ->each(fn (SplFileInfo $file) => $this->filesystem->delete($file->getRealPath()));
    }

    private function getPhpFilesWithTranslationsAbsolutePaths(Collection $apiTranslationsDto): Collection
    {
        return $apiTranslationsDto
            ->flatMap(function (ApiTranslationsDto $apiTranslationsDto) {
                return collect($apiTranslationsDto->phpData)
                    ->filter()
                    ->map(function (string $translation, string $key) use ($apiTranslationsDto) {
                        $fileName = pathinfo(explode('.', $key)[0], PATHINFO_FILENAME);
                        $relativeDirectory = pathinfo($key, PATHINFO_DIRNAME);
                        $absoluteDirectory = lang_path($apiTranslationsDto->locale . DIRECTORY_SEPARATOR . $relativeDirectory);
                        $absoluteDirectory = rtrim($absoluteDirectory, DIRECTORY_SEPARATOR . '.');
                        return $absoluteDirectory . DIRECTORY_SEPARATOR . "{$fileName}.php";
                    })
                    ->values();
            })
            ->unique();
    }
}
