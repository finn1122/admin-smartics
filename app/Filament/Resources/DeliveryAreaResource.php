<?php

namespace App\Filament\Resources;

use App\Filament\Components\PolygonMap;
use App\Filament\Resources\DeliveryAreaResource\Pages;
use App\Filament\Resources\DeliveryAreaResource\RelationManagers;
use App\Models\DeliveryArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class DeliveryAreaResource extends Resource
{
    protected static ?string $model = DeliveryArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag'; // 🚩 Para áreas delimitadas
    protected static ?string $navigationGroup = 'Shop';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Área')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('price')
                    ->label('Precio de Envío')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->columnSpan(1),

                Forms\Components\Toggle::make('active')
                    ->label('Activo')
                    ->required()
                    ->default(true)
                    ->columnSpan(1),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),

                PolygonMap::make('coordinates')
                    ->label('Delivery Area')
                    ->required()
                    ->columnSpanFull()
            ])
            ->columns(3); // Organiza los campos en 3 columnas (excepto los full span)
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Solo áreas activas')
                    ->query(fn ($query) => $query->where('active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDeliveryAreas::route('/'),
            'create' => Pages\CreateDeliveryArea::route('/create'),
            'edit' => Pages\EditDeliveryArea::route('/{record}/edit'),
        ];
    }
}
