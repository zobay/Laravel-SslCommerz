<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zobay\LaravelSslCommerz\Commands\LaravelSslCommerzCommand;

class LaravelSslCommerzServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-sslcommerz')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_sslcommerz_table')
            ->hasCommand(LaravelSslCommerzCommand::class);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(LaravelSslCommerz::class, fn () => new LaravelSslCommerz());
    }
}
