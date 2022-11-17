<?php

namespace UseLocale\LocaleLaravel\Read;

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

class GetTranslationsDtoAction
{
    private GetPhpTranslationsAction $getPhpTranslationsAction;
    private GetJsonTranslationsAction $getJsonTranslationsAction;
    private ScannerService $scannerService;

    public function __construct(
        GetPhpTranslationsAction $getPhpTranslationsAction,
        GetJsonTranslationsAction $getJsonTranslationsAction,
        ScannerService $scannerService
    )
    {
        $this->getPhpTranslationsAction = $getPhpTranslationsAction;
        $this->getJsonTranslationsAction = $getJsonTranslationsAction;
        $this->scannerService = $scannerService;
    }

    public function __invoke(string $locale): ApiTranslationsDto
    {
        $translations = new ApiTranslationsDto(
            $locale,
            ($this->getJsonTranslationsAction)($locale),
            ($this->getPhpTranslationsAction)($locale)
        );

        $this->scannerService->scan();

        $jsonFileKeys = $this->getMissingKeys(array_keys($translations->jsonData), $this->scannerService->getJsonKeys());
        $phpFileKeys = $this->getMissingKeys(array_keys($translations->phpData), $this->scannerService->getPhpKeys());

        $translations->jsonData = array_merge($translations->jsonData, $jsonFileKeys);
        $translations->phpData = array_merge($translations->phpData, $phpFileKeys);

        return $translations;
    }

    private function getMissingKeys(array $translatedKeys, array $keysToCheck): array
    {
        $diffKeys = array_diff($keysToCheck, $translatedKeys);

        return collect($diffKeys)->mapWithKeys(fn(string $key) => [$key => null])->toArray();
    }
}
