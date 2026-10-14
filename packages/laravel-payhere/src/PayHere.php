<?php

namespace Lahirulhr\PayHere;

class PayHere
{
    public static function checkOut(): Checkout
    {
        return new Checkout();
    }
}
