<?php

namespace Zobay\LaravelSslCommerz\Commands;

use Illuminate\Console\Command;

class LaravelSslCommerzCommand extends Command
{
    public $signature = 'laravel-sslcommerz';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
