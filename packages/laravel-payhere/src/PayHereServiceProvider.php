<?php

namespace Lahirulhr\PayHere;

use Illuminate\Support\ServiceProvider;

class PayHereServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payhere.php', 'payhere');
    }
}
