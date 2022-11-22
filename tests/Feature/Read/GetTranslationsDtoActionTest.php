<?php

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Read\GetTranslationsDtoAction;

test('dto structure is correct', function () {
    /** @var ApiTranslationsDto $apiTranslationsDto */
    $apiTranslationsDto = resolve(GetTranslationsDtoAction::class)('en');

    expect($apiTranslationsDto)
        ->toBeInstanceOf(ApiTranslationsDto::class)
        ->toHaveProperty('locale')
        ->toHaveProperty('jsonData')
        ->toHaveProperty('phpData');
});

test('dto have got translations', function () {
    /** @var ApiTranslationsDto $apiTranslationsDto */
    $apiTranslationsDto = resolve(GetTranslationsDtoAction::class)('en');
    expect($apiTranslationsDto->locale)->toBe('en');

    expect($apiTranslationsDto->jsonData)
        ->toBeArray()
        ->toBe([
            'foo' => 'bar',
            'breeze' => 'alpine',
            'JSON trans' => null,
            'JSON trans_choice' => null,
            'JSON Lang::get' => null,
            'JSON Lang::choice' => null,
            'JSON Lang::trans' => null,
            'JSON Lang::transChoice' => null,
            'JSON @lang' => null,
            'JSON @choice' => null,
            'JSON __' => null,
        ]);

    expect($apiTranslationsDto->phpData)
        ->toBeArray()
        ->toBe([
            'norf' . DIRECTORY_SEPARATOR . 'fubar.passport' => 'horizon',
            'quux.lorem' => 'ipsum',
            'quux.taylor' => 'otwell',
            'php.trans' => null,
            'php.trans_choice' => null,
            'php.Lang::get' => null,
            'php.Lang::choice' => null,
            'php.Lang::trans' => null,
            'php.Lang::transChoice' => null,
            'php.@lang' => null,
            'php.@choice' => null,
            'php.__' => null,
        ]);
});
