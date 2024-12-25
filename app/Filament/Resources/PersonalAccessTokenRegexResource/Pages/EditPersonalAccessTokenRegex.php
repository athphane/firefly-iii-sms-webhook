<?php

namespace App\Filament\Resources\PersonalAccessTokenRegexResource\Pages;

use App\Filament\Resources\PersonalAccessTokenRegexResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersonalAccessTokenRegex extends EditRecord
{
    protected static string $resource = PersonalAccessTokenRegexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
