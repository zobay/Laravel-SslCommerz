<?php

namespace Zobay\LaravelSslCommerz;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zobay\LaravelSslCommerz\Commands\LaravelSslCommerzCommand;

class LaravelSslCommerzServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-sslcommerz')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_sslcommerz_table')
            ->hasCommand(LaravelSslCommerzCommand::class);
    }
}
