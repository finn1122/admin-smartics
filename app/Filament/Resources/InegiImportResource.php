<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InegiImportResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InegiImportResource extends Resource
{
    protected static ?string $model = null; // No usamos modelo Eloquent

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationLabel = 'Importar INEGI';

    protected static ?string $modelLabel = 'Importación INEGI';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Importar Datos Postales')
                    ->description('Suba el archivo Excel oficial del INEGI')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
                            ->label('Archivo Excel')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel'
                            ])
                            ->directory('inegi-imports')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable(),

                        Forms\Components\Toggle::make('truncate')
                            ->label('¿Eliminar datos existentes antes de importar?')
                            ->helperText('Marcar esta opción borrará todos los datos postales actuales')
                            ->default(false),
                    ])
                    ->columns(1)
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ImportInegiData::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
