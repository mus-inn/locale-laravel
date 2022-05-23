<?php

use Illuminate\Translation\Translator;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Write\WriteJsonTranslationsAction;

it('can writes json files translations', function (ApiTranslationsDto $apiTranslationsDto) {
    app()->setLocale('ca');

    expect([
        __('foo'),
        __('breeze'),
    ])->toBe(['baz', 'inertia']);

    resolve(WriteJsonTranslationsAction::class)($apiTranslationsDto->locale, $apiTranslationsDto->jsonData, 'en');

    /** @var Translator $translator */
    $translator = resolve(Translator::class);
    $translator->setLoaded([]);

    expect([
        __('foo'),
        __('breeze'),
    ])->toBe(['fubar', 'world']);
})->with('apiTranslationDto');
