<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Support\FireflyIII\Facades\FireflyIII;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $slug = 'transactions';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make(__('Transaction Details'))
                                    ->schema([
                                        TextInput::make('card')
                                            ->inlineLabel()
                                            ->required()
                                            ->numeric(),

                                        DatePicker::make('transaction_at')
                                            ->inlineLabel()
                                            ->label('Transaction Date'),

                                        TextInput::make('currency')
                                            ->inlineLabel()
                                            ->required(),

                                        TextInput::make('amount')
                                            ->inlineLabel()
                                            ->required()
                                            ->numeric(),

                                        TextInput::make('location')
                                            ->inlineLabel()
                                            ->required(),

                                        TextInput::make('approval_code')
                                            ->inlineLabel()
                                            ->required(),

                                        TextInput::make('reference_no')
                                            ->inlineLabel()
                                            ->required(),

                                        TextInput::make('message')
                                            ->inlineLabel()
                                            ->required(),
                                    ]),

                                Section::make(__('Related Vendor'))
                                    ->schema([
                                        Select::make('vendor_id')
                                            ->hiddenLabel(true)
                                            ->relationship('vendor', 'name')
                                            ->searchable()
                                            ->required(),
                                    ]),
                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Created Date')
                                    ->content(fn(?Transaction $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label('Last Modified Date')
                                    ->content(fn(?Transaction $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                            ])
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vendor.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currency'),
                TextColumn::make('amount'),

                TextColumn::make('transaction_at')
                    ->label('Transaction Date')
                    ->date(),

                IconColumn::make('firefly_transaction_id')
                    ->icon(function ($record) {
                        return match ($record->firefly_transaction_id) {
                            null => 'heroicon-o-circle-slash',
                            default => 'heroicon-o-check-circle',
                        };
                    })
                    ->color(fn($state) => match ($state) {
                        null => 'danger',
                        default => 'success',
                    })

            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('open-in-ff')
                    ->label('In FF')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('slate')
                    ->hidden(fn($record) => !isset($record->firefly_transaction_id))
                    ->url(function ($record) {
                        return FireflyIII::getTransactionUrl($record->firefly_transaction_id);
                    }),

                Action::make('sync-transaction')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->hidden(fn($record) => isset($record->firefly_transaction_id))
                    ->action(function ($record) {
                        $record->process();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['vendor']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['vendor.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->vendor) {
            $details['Vendor'] = $record->vendor->name;
        }

        return $details;
    }
}
