<?php

use UseLocale\LocaleLaravel\Read\ScannerService;

test("scanner service reads views translations", function () {
    /** @var ScannerService $scanner */
    $scanner = resolve(ScannerService::class);
    $scanner->scan();

    expect($scanner->getJsonKeys())
        ->toBeArray()
        ->toBe([
            'JSON trans',
            'JSON trans_choice',
            'JSON Lang::get',
            'JSON Lang::choice',
            'JSON Lang::trans',
            'JSON Lang::transChoice',
            'JSON @lang',
            'JSON @choice',
            'JSON __',
        ]);

    expect($scanner->getPhpKeys())
        ->toBeArray()
        ->toBe([
            'php.trans',
            'php.trans_choice',
            'php.Lang::get',
            'php.Lang::choice',
            'php.Lang::trans',
            'php.Lang::transChoice',
            'php.@lang',
            'php.@choice',
            'php.__',
        ]);
});
