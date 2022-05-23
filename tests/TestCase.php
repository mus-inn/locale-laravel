<?php

namespace UseLocale\LocaleLaravel\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use UseLocale\LocaleLaravel\LocaleServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Filesystem $filesystem */
        $filesystem = resolve(Filesystem::class);

        $filesystem->deleteDirectory(lang_path());
        $filesystem->copyDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'lang', lang_path());
    }

    protected function getPackageProviders($app)
    {
        return [
            LocaleServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_locale-laravel_table.php.stub';
        $migration->up();
        */
    }
}
