<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentProcessorResource\Pages;
use App\Filament\Resources\PaymentProcessorResource\RelationManagers;
use App\Models\PaymentProcessor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class PaymentProcessorResource extends Resource
{
    protected static ?string $model = PaymentProcessor::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $modelLabel = 'Procesador de Pago';
    protected static ?string $pluralModelLabel = 'Procesadores de Pago';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'paypal' => 'PayPal',
                                'stripe' => 'Stripe',
                                'mercadopago' => 'MercadoPago',
                                'other' => 'Otro',
                            ])
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(false),

                        Forms\Components\Placeholder::make('credentials_info')
                            ->label('Credenciales')
                            ->content('Las credenciales se configuran en el archivo .env')
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

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paypal' => 'info',
                        'stripe' => 'primary',
                        'mercadopago' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_configured')
                    ->label('Configurado')
                    ->boolean()
                    ->state(fn (PaymentProcessor $record): bool => $record->is_configured),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'mercadopago' => 'MercadoPago',
                    ]),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado activo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paypal' => 'info',
                                'stripe' => 'primary',
                                'mercadopago' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\IconEntry::make('active')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_configured')
                            ->boolean()
                            ->label('Credenciales configuradas'),
                    ])->columns(2),
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
