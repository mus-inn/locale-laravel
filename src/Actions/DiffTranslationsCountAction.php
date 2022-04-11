<?php

namespace Localizy\LocalizyLaravel\Actions;

use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;

class DiffTranslationsCountAction
{
    public function __invoke(ApiTranslationsDto $currentTranslationsDto, ApiTranslationsDto $newTranslationsDto): int
    {
        $phpModifiedTranslationsCount = $this->countDiff($newTranslationsDto->phpData, $currentTranslationsDto->phpData);
        $jsonModifiedCount = $this->countDiff($newTranslationsDto->jsonData, $currentTranslationsDto->jsonData);

        return ($phpModifiedTranslationsCount + $jsonModifiedCount);
    }

    private function countDiff(array $newTranslations, array $currentTranslations): int
    {
        $collection = collect($newTranslations);
        $diff = $collection->diffAssoc($currentTranslations);

        return $diff->count();
    }
}
