<?php

namespace UseLocale\LocaleLaravel;

use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use UseLocale\LocaleLaravel\Commands\SetupCommand;
use UseLocale\LocaleLaravel\Commands\SyncCommand;

class LocaleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('locale-laravel')
            ->hasCommands([
                SetupCommand::class,
                SyncCommand::class,
            ]);

        $this->app->singleton(Locale::class, function () {
            return new Locale(
                resolve(Filesystem::class),
                config('services.locale.base_url', 'https://app.uselocale.com/api/v1'),
                config('services.locale.key') ?? '',
            );
        });
    }
}
