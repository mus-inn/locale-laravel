<?php

use UseLocale\LocaleLaravel\Read\GetPhpTranslationsAction;

test('source language php file has got translations', function () {
    $translations = resolve(GetPhpTranslationsAction::class)('en');
    expect($translations)
        ->toBeArray()
        ->toHaveKey('norf' . DIRECTORY_SEPARATOR . 'fubar.passport', 'horizon')
        ->toHaveKey('quux.lorem', 'ipsum');
});

test("target language php file hasn't got translations", function () {
    $translations = resolve(GetPhpTranslationsAction::class)('missingLocale');
    expect($translations)
        ->toBeArray()
        ->toBeEmpty();
});
