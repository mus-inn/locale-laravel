<?php

namespace UseLocale\LocaleLaravel\Read;

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

class GetTranslationsDtoAction
{
    private GetPhpTranslationsAction $getPhpTranslationsAction;
    private GetJsonTranslationsAction $getJsonTranslationsAction;

    public function __construct(
        GetPhpTranslationsAction $getPhpTranslationsAction,
        GetJsonTranslationsAction $getJsonTranslationsAction
    ) {
        $this->getPhpTranslationsAction = $getPhpTranslationsAction;
        $this->getJsonTranslationsAction = $getJsonTranslationsAction;
    }

    public function __invoke(string $locale): ApiTranslationsDto
    {
        return new ApiTranslationsDto(
            $locale,
            ($this->getJsonTranslationsAction)($locale),
            ($this->getPhpTranslationsAction)($locale)
        );
    }
}
