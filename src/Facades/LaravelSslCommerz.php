<?php

namespace Zobay\LaravelSslCommerz\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Zobay\LaravelSslCommerz\LaravelSslCommerz
 */
class LaravelSslCommerz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Zobay\LaravelSslCommerz\LaravelSslCommerz::class;
    }
}
