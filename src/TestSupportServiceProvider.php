<?php

namespace Tipoff\TestSupport;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\TestSupport\Commands\TestSupportCommand;

class TestSupportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('test-support')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_test_support_table')
            ->hasCommand(TestSupportCommand::class);
    }
}
