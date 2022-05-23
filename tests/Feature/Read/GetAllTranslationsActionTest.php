<?php

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Read\GetAllTranslationsAction;

it('returns array of dto translations', function () {
    $translationsDtos = resolve(GetAllTranslationsAction::class)();

    expect($translationsDtos)
        ->toBeArray()
        ->toHaveCount(2);

    expect($translationsDtos[0])
        ->toBeInstanceOf(ApiTranslationsDto::class);

    expect(
        collect($translationsDtos)->pluck('locale')->sort()->toArray()
    )->toBe(['ca', 'en']);
});
