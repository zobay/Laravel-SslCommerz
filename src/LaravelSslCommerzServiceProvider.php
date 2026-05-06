<?php

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
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

    public function packageBooted(): void
    {
        Http::macro('sslcommerz', function () {
            $baseUrl = config('sslcommerz.sandbox')
                ? config('sslcommerz.sandbox_base_url')
                : config('sslcommerz.live_base_url');

            $version = config('sslcommerz.version');

            return Http::baseUrl("{$baseUrl}/gwprocess/{$version}")
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept'       => 'application/json',
                ]);
        });

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/sslcommerz.php' => config_path('sslcommerz.php'),
            ], 'config');
        }
    }
}
