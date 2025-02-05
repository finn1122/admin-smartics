<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

                // Campo para el código CVA
                Forms\Components\TextInput::make('cva_key')
                    ->label('Código CVA')
                    ->required()
                    ->maxLength(255),

                // Campo para el SKU
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255),

                // Campo para la garantía
                Forms\Components\TextInput::make('warranty')
                    ->label('Garantía')
                    ->required(),

                // Campo para el estado activo/inactivo
                Forms\Components\Toggle::make('active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna para el nombre de la marca
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->limit(30)
                    ->sortable( )
                    ->searchable(),


                Tables\Columns\TextColumn::make('cva_key')
                    ->label('Código CVA')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('warranty')
                    ->label('Garantía'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                // Columna para el nombre de la marca
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable()
                    ->searchable(),

                // Usar IconColumn para el campo "active"
                Tables\Columns\IconColumn::make('active')
                    ->label('Activa')
                    ->boolean() // Convierte el valor en un ícono de check (true) o cruz (false)
                    ->trueIcon('heroicon-o-check-circle') // Ícono para "true"
                    ->falseIcon('heroicon-o-x-circle'),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
