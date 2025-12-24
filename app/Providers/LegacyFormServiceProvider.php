<?php

namespace App\Providers;

use App\Support\LegacyFormBuilder;
use Illuminate\Support\ServiceProvider;

class LegacyFormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('form', function () {
            return new LegacyFormBuilder();
        });

        $this->app->alias('form', LegacyFormBuilder::class);
    }
}


