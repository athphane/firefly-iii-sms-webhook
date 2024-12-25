<?php

namespace App\Filament\Resources\PersonalAccessTokenRegexResource\Pages;

use App\Filament\Resources\PersonalAccessTokenRegexResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonalAccessTokenRegexes extends ListRecords
{
    protected static string $resource = PersonalAccessTokenRegexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
