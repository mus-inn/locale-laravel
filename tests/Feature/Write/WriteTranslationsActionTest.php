<?php

use Illuminate\Translation\Translator;
use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;
use UseLocale\LocaleLaravel\Write\WriteTranslationsAction;

it('can write translations files', function (ApiTranslationsDto $apiTranslationsDto) {
    app()->setLocale('ca');

    expect([
        __('norf' . DIRECTORY_SEPARATOR . 'fubar.passport'),
        __('quux.lorem'),
        __('foo'),
        __('breeze'),
    ])->toBe(['socialite', 'dolor', 'baz', 'inertia']);

    /** @var WriteTranslationsAction $action */
    $action = resolve(WriteTranslationsAction::class);
    $action($apiTranslationsDto, 'en');

    /** @var Translator $translator */
    $translator = resolve(Translator::class);
    $translator->setLoaded([]);

    expect([
        __('norf' . DIRECTORY_SEPARATOR . 'fubar.passport'),
        __('quux.lorem'),
        __('foo'),
        __('breeze'),
    ])->toBe(['spark', 'forge', 'fubar', 'world']);
})->with('apiTranslationDto');
