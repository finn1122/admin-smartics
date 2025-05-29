<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentProcessorResource\Pages;
use App\Filament\Resources\PaymentProcessorResource\RelationManagers;
use App\Models\PaymentProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentProcessorResource extends Resource
{
    protected static ?string $model = PaymentProcessor::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de cuenta')
                    ->schema([
                        Forms\Components\TextInput::make('processor_client_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('processor_key')
                            ->password()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('processor_client_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        '1' => 'Activos',
                        '0' => 'Inactivos',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentMethodsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentProcessors::route('/'),
            'create' => Pages\CreatePaymentProcessor::route('/create'),
            //'view' => Pages\ViewPaymentProcessor::route('/{record}'),
            'edit' => Pages\EditPaymentProcessor::route('/{record}/edit'),
        ];
    }
}
