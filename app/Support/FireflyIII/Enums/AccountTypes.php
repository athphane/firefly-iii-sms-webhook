<?php

namespace App\Support\FireflyIII\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountTypes: string implements HasLabel, HasColor
{
    case ASSET = 'asset';
    case EXPENSE = 'expense';
    case REVENUE = 'revenue';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ASSET => __('Asset'),
            self::EXPENSE => __('Expense'),
            self::REVENUE => __('Revenue'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ASSET => 'blue',
            self::EXPENSE => 'danger',
            self::REVENUE => 'success',
        };
    }
}
