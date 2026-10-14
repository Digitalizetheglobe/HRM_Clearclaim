<?php

namespace Lahirulhr\PayHere;

use Illuminate\Support\Facades\Facade;

class PayHereFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PayHere::class;
    }

    public static function checkOut(): Checkout
    {
        return PayHere::checkOut();
    }
}
