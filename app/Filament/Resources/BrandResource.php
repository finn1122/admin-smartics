<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Marcas';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Marcas'; // Cambia "Lotes" por el nombre plural que desees
    }

    public static function getLabel(): ?string
    {
        return 'Marca'; // Nombre en singular
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para el nombre de la marca
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la marca')
                    ->required()
                    ->maxLength(255),

                // Campo para el estado activo/inactivo
                Forms\Components\Toggle::make('active')
                    ->label('Activa')
                    ->default(true), // Valor por defecto
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna para el nombre de la marca
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                // Usar IconColumn para el campo "active"
                Tables\Columns\IconColumn::make('active')
                    ->label('Activa')
                    ->boolean() // Convierte el valor en un ícono de check (true) o cruz (false)
                    ->trueIcon('heroicon-o-check-circle') // Ícono para "true"
                    ->falseIcon('heroicon-o-x-circle'), // Ícono para "false"
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
