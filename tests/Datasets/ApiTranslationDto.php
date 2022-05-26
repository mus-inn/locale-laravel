<?php

use UseLocale\LocaleLaravel\DTOs\ApiTranslationsDto;

dataset('apiTranslationDto', [
    new ApiTranslationsDto(
        'ca',
        [
            'foo' => 'fubar',
            'breeze' => 'world',
        ],
        [
            'norf/fubar.passport' => 'spark',
            'quux.lorem' => 'forge',
        ]
    )
]);
