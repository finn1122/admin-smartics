<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryTypeResource\Pages;
use App\Models\DeliveryType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryTypeResource extends Resource
{
    protected static ?string $model = DeliveryType::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Identificador único (usado internamente)'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de envío')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),

                        Forms\Components\Toggle::make('is_free')
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                $set('price', $state ? 0 : 4.99);
                            }),

                        Forms\Components\TextInput::make('estimated_days_min')
                            ->numeric()
                            ->label('Días mínimos'),

                        Forms\Components\TextInput::make('estimated_days_max')
                            ->numeric()
                            ->label('Días máximos'),

                        Forms\Components\Toggle::make('active')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Metadata adicional')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->keyLabel('Propiedad')
                            ->valueLabel('Valor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_free')
                    ->boolean(),

                Tables\Columns\TextColumn::make('estimated_range')
                    ->label('Tiempo entrega')
                    ->getStateUsing(function (DeliveryType $record) {
                        return $record->estimated_range ?? 'N/A';
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListDeliveryTypes::route('/'),
            'create' => Pages\CreateDeliveryType::route('/create'),
            'edit' => Pages\EditDeliveryType::route('/{record}/edit'),
        ];
    }
}
