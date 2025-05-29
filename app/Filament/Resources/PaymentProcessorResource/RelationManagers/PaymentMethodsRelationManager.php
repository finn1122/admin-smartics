<?php

namespace App\Filament\Resources\PaymentProcessorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentMethods';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('method_type')
                    ->options([
                        'credit_card' => 'Tarjeta de crédito',
                        'debit_card' => 'Tarjeta de débito',
                        'bank_transfer' => 'Transferencia bancaria',
                        'digital_wallet' => 'Billetera digital',
                        'cash' => 'Efectivo',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('instructions')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('method_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'credit_card' => 'Tarjeta crédito',
                        'debit_card' => 'Tarjeta débito',
                        'bank_transfer' => 'Transferencia',
                        'digital_wallet' => 'Billetera',
                        'cash' => 'Efectivo',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
