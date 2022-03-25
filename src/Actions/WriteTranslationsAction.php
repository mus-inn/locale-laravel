<?php

namespace Localizy\LocalizyLaravel\Actions;


use Localizy\LocalizyLaravel\DTOs\ApiTranslationsDto;

class WriteTranslationsAction
{
    private WriteJsonTranslationsAction $writeJsonTranslationsAction;
    private WritePhpTranslationsAction $writePhpTranslationsAction;

    public function __construct(
        WriteJsonTranslationsAction $writeJsonTranslationsAction,
        WritePhpTranslationsAction $writePhpTranslationsAction
    )
    {
        $this->writeJsonTranslationsAction = $writeJsonTranslationsAction;
        $this->writePhpTranslationsAction = $writePhpTranslationsAction;
    }

    public function __invoke(ApiTranslationsDto $apiTranslationsDto, string $sourceLocale): void
    {
        ($this->writeJsonTranslationsAction)($apiTranslationsDto->locale, $apiTranslationsDto->jsonData);
        ($this->writePhpTranslationsAction)($apiTranslationsDto->locale, $apiTranslationsDto->phpData, $sourceLocale);
    }
}
