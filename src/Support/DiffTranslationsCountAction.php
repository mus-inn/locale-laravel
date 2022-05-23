<?php

namespace UseLocale\LocaleLaravel\Support;

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

class DiffTranslationsCountAction
{
    public function __invoke(ApiTranslationsDto $currentTranslationsDto, ApiTranslationsDto $newTranslationsDto): int
    {
        $numPhpModifiedTranslations = $this->countDifferences($newTranslationsDto->phpData, $currentTranslationsDto->phpData);
        $numJsonModifiedTranslations = $this->countDifferences($newTranslationsDto->jsonData, $currentTranslationsDto->jsonData);

        return ($numPhpModifiedTranslations + $numJsonModifiedTranslations);
    }

    private function countDifferences(array $newTranslations, array $currentTranslations): int
    {
        return collect($newTranslations)
            ->diffAssoc($currentTranslations)
            ->count();
    }
}
