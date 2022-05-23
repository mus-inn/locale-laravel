<?php

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Read\GetTranslationsDtoAction;

test('dto have got translations', function () {
    /** @var ApiTranslationsDto $apiTranslationsDto */
    $apiTranslationsDto = resolve(GetTranslationsDtoAction::class)('en');

    expect($apiTranslationsDto)
        ->toBeInstanceOf(ApiTranslationsDto::class)
        ->toHaveProperties([
            'locale',
            'jsonData',
            'phpData',
        ]);

    expect($apiTranslationsDto->locale)->toBe('en');

    expect($apiTranslationsDto->jsonData)
        ->toBeArray()
        ->toHaveKey('foo', 'bar');

    expect($apiTranslationsDto->phpData)
        ->toBeArray()
        ->toHaveKey('norf' . DIRECTORY_SEPARATOR . 'fubar.passport', 'horizon')
        ->toHaveKey('quux.lorem', 'ipsum');
});

test("dto havent got translations", function () {
    /** @var ApiTranslationsDto $apiTranslationsDto */
    $apiTranslationsDto = resolve(GetTranslationsDtoAction::class)('missingLocale');

    expect($apiTranslationsDto)
        ->toBeInstanceOf(ApiTranslationsDto::class)
        ->toHaveProperties([
            'locale',
            'jsonData',
            'phpData',
        ]);

    expect($apiTranslationsDto->locale)->toBe('missingLocale');

    expect($apiTranslationsDto->jsonData)
        ->toBeArray()
        ->toBeEmpty();

    expect($apiTranslationsDto->phpData)
        ->toBeArray()
        ->toBeEmpty();
});
