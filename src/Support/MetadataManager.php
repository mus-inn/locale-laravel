<?php

namespace UseLocale\LocaleLaravel\Support;

use Illuminate\Filesystem\Filesystem;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

class MetadataManager
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    private function getMetaDataFilePath(?string $filePath = null): string
    {
        return lang_path('.uselocale') . ($filePath ? (DIRECTORY_SEPARATOR . $filePath) : '');
    }

    public function getLastSyncTimestamp(): int
    {
        return ($this->filesystem->exists($this->getMetaDataFilePath('timestamp')))
            ? intval($this->filesystem->get($this->getMetaDataFilePath('timestamp')))
            : 0;
    }

    public function touchTimestamp(): void
    {
        $this->filesystem->ensureDirectoryExists($this->getMetaDataFilePath());

        $this->filesystem->put(
            $this->getMetaDataFilePath('timestamp'),
            (string)time()
        );
    }

    public function updateSnapshot(ApiTranslationsDto $apiTranslationsDto): void
    {
        $this->filesystem->ensureDirectoryExists($this->getMetaDataFilePath());

        $this->filesystem->put(
            $this->getMetaDataFilePath('snapshot'),
            json_encode($apiTranslationsDto)
        );
    }

    public function getSnapshotContent(): ?string
    {
        return ($this->filesystem->exists($this->getMetaDataFilePath('snapshot')))
            ? $this->filesystem->get($this->getMetaDataFilePath('snapshot'))
            : null;
    }
}
