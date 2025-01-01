<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync-vendors')
                ->label('Sync Vendors')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    Artisan::call('firefly:sync-vendors');

                    Notification::make('success')
                        ->success()
                        ->title('Vendors Synced')
                        ->body('Vendors synced successfully.')
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
