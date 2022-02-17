<?php

namespace Localizy\LocalizyLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Localizy\LocalizyLaravel\Localizy
 */
class LocalizyLaravel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'localizy-laravel';
    }
}
