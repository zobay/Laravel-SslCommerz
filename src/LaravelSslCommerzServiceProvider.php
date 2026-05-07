<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zobay\LaravelSslCommerz\Commands\LaravelSslCommerzCommand;

class LaravelSslCommerzServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-sslcommerz')
            ->hasConfigFile('sslcommerz')
            ->hasViews()
            ->hasCommand(LaravelSslCommerzCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LaravelSslCommerz::class, fn () => new LaravelSslCommerz());
    }

    public function packageBooted(): void
    {
        Http::macro('sslcommerz', function () {
            $baseUrl = config('sslcommerz.sandbox')
                ? config('sslcommerz.sandbox_base_url')
                : config('sslcommerz.live_base_url');

            return Http::baseUrl($baseUrl)->asForm();
        });
    }
}
