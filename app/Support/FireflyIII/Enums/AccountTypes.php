<?php

namespace App\Support\FireflyIII\Enums;

enum AccountTypes: string
{
    case ASSET = 'asset';
    case EXPENSE = 'expense';
    case REVENUE = 'revenue';
}
