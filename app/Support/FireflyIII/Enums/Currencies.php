<?php

namespace App\Support\FireflyIII\Enums;

enum Currencies: string
{
    case USD = 'USD';
    case MVR = 'MVR';
    case EUR = 'EUR';

    public function exchangeRate(): float
    {
        return match ($this) {
            self::USD => 15.42,
            self::EUR => 16.20,
            self::MVR => 1,
        };
    }
}
