<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalAccessTokenRegexResource\Pages;
use App\Filament\Resources\PersonalAccessTokenRegexResource\RelationManagers;
use App\Models\PersonalAccessTokenRegex;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonalAccessTokenRegexResource extends Resource
{
    protected static ?string $model = PersonalAccessTokenRegex::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('personal_access_token_id')
                            ->label('Personal Access Token')
                            ->native(false)
                            ->relationship('personalAccessToken', 'name')
                            ->helperText(__('Select the PAT that will handle incoming transactions for this regex.'))
                        ,


                        Forms\Components\Textarea::make('regex')
                            ->required()
                            ->rows(4)
                            ->autosize()
                            ->columnSpanFull()
                            ->helperText(__('Your Regex pattern, should at the minimum, have capture groups for the following fields: vendor, currency, amount, transaction_date.')),

                        Forms\Components\Toggle::make('allowed')
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('personalAccessToken.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('allowed')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPersonalAccessTokenRegexes::route('/'),
            'create' => Pages\CreatePersonalAccessTokenRegex::route('/create'),
            'edit'   => Pages\EditPersonalAccessTokenRegex::route('/{record}/edit'),
        ];
    }


}
