<?php

use UseLocale\LocaleLaravel\Read\GetJsonTranslationsAction;

test('source language json file has got translations', function () {
    $translations = resolve(GetJsonTranslationsAction::class)('en');
    expect($translations)
        ->toBeArray()
        ->toHaveKey('foo', 'bar');
});

test("target language json file hasn't got translations", function () {
    $translations = resolve(GetJsonTranslationsAction::class)('missingLocale');
    expect($translations)
        ->toBeArray()
        ->toBeEmpty();
});
