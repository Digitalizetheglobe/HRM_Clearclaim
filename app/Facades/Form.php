<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Compatibility facade to replace laravelcollective/html's Form facade.
 *
 * This keeps existing Blade templates working while moving off the abandoned package.
 */
class Form extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'form';
    }
}


