<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync-transaction')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->hidden(fn($record) => isset($record->firefly_transaction_id))
                ->action(function ($record) {
                    $record->process();
                }),

            DeleteAction::make(),
        ];
    }
}
