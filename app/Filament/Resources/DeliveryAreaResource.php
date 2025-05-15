<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryAreaResource\Pages;
use App\Filament\Resources\DeliveryAreaResource\RelationManagers;
use App\Models\DeliveryArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Livewire;
use App\Livewire\MapAreaSelector;


class DeliveryAreaResource extends Resource
{
    protected static ?string $model = DeliveryArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del área')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio de envío')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Delimitación geográfica')
                    ->schema([
                        Livewire::make(MapAreaSelector::class)
                            ->label('Dibuja el área de cobertura')
                            ->afterStateUpdated(function ($state, $set) {
                                $set('coordinates', $state);
                            })
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('coordinates')
                    ]),

                Forms\Components\Section::make('Configuración adicional')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Área activa')
                            ->default(true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ]),
            ]);
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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Solo áreas activas')
                    ->query(fn ($query) => $query->where('is_active', true)),
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
