<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InegiPostalDataResource\Pages;
use App\Filament\Resources\InegiPostalDataResource\RelationManagers;
use App\Models\InegiPostalData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InegiPostalDataResource extends Resource
{
    protected static ?string $model = InegiPostalData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Código Postal';
    protected static ?string $pluralModelLabel = 'Códigos Postales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListInegiPostalData::route('/'),
            'create' => Pages\CreateInegiPostalData::route('/create'),
            'edit' => Pages\EditInegiPostalData::route('/{record}/edit'),
            'import' => Pages\ImportInegiPostalData::route('/import'),
        ];
    }
}
