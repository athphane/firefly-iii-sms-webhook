<?php

namespace App\Support\FireflyIII\Enums;

enum Currencies: string
{
    case USD = 'USD';
    case MVR = 'MVR';

    public function exchangeRate(): float
    {
        return match ($this) {
            self::USD => 15.42,
            self::MVR => 1,
        };
    }
}
