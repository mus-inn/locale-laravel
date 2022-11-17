<?php

use Illuminate\Translation\Translator;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Write\WritePhpTranslationsAction;

it('can writes php files translations', function (ApiTranslationsDto $apiTranslationsDto) {
    app()->setLocale('ca');

    expect([
        __('norf' . DIRECTORY_SEPARATOR . 'fubar.passport'),
        __('quux.lorem'),
    ])->toBe(['socialite', 'dolor']);

    resolve(WritePhpTranslationsAction::class)($apiTranslationsDto->locale, $apiTranslationsDto->phpData);

    /** @var Translator $translator */
    $translator = resolve(Translator::class);
    $translator->setLoaded([]);

    expect([
        __('norf' . DIRECTORY_SEPARATOR . 'fubar.passport'),
        __('quux.lorem'),
    ])->toBe(['spark', 'forge']);
})->with('apiTranslationDto');
