<?php

namespace UseLocale\LocaleLaravel\Support;

use Illuminate\Support\Collection;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Read\GetTranslationsDtoAction;

class CalculateTotalChangedTranslationsAction
{
    private DiffTranslationsCountAction $diffTranslationsCountAction;
    private GetTranslationsDtoAction $getTranslationsDtoAction;

    public function __construct(
        DiffTranslationsCountAction $diffTranslationsCountAction,
        GetTranslationsDtoAction $getTranslationsDtoAction
    ) {
        $this->diffTranslationsCountAction = $diffTranslationsCountAction;
        $this->getTranslationsDtoAction = $getTranslationsDtoAction;
    }

    public function __invoke(Collection $apiTranslationsDto): int
    {
        /** @var (callable(ApiTranslationsDto): int) $calculateChangedTranslationsCallback */
        $calculateChangedTranslationsCallback = function (ApiTranslationsDto $apiTranslationsDto) {
            $currentTranslationsDto = ($this->getTranslationsDtoAction)($apiTranslationsDto->locale);

            return ($this->diffTranslationsCountAction)($currentTranslationsDto, $apiTranslationsDto);
        };

        return $apiTranslationsDto->sum($calculateChangedTranslationsCallback);
    }
}
