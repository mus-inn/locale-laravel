<?php

namespace Localizy\LocalizyLaravel;

use Localizy\LocalizyLaravel\Commands\SetupCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LocalizyLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('localizy-laravel')
            ->hasConfigFile('localizy')
            ->hasCommands([
                SetupCommand::class,
            ]);

        $this->app->singleton(Localizy::class, function () {
            return new Localizy(
                config('localizy.base_url', 'https://localizy.app/api/v1'),
                config('localizy.key') ?? '',
            );
        });
    }
}
