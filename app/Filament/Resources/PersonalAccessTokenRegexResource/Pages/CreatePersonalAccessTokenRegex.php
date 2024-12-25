<?php

namespace App\Filament\Resources\PersonalAccessTokenRegexResource\Pages;

use App\Filament\Resources\PersonalAccessTokenRegexResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreatePersonalAccessTokenRegex extends CreateRecord
{
    protected static string $resource = PersonalAccessTokenRegexResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Create Access Token Regex');
    }
}
