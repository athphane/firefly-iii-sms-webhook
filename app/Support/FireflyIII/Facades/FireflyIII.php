<?php

namespace App\Support\FireflyIII\Facades;

use Illuminate\Support\Facades\Facade;

class FireflyIII extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'firefly-iii';
    }
}
