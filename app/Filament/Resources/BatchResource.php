<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages;
use App\Models\Batch;
use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Actions\Action;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Lotes';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Lotes'; // Cambia "Lotes" por el nombre plural que desees
    }

    public static function getLabel(): ?string
    {
        return 'Lote'; // Nombre en singular
    }


    public static function form(Form $form): Form
    {
        // Obtén una instancia del servicio DocumentUrlService
        $documentUrlService = app(DocumentUrlService::class);

        return $form
            ->schema([
                // Sección: Información del Producto
                Forms\Components\Section::make('Información del Producto')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->label('Producto')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->label('Proveedor'),
                    ])
                    ->collapsible(),

                // Sección: Precios y Cantidad
                Forms\Components\Section::make('Precios y Cantidad')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('Cantidad'),

                        Forms\Components\TextInput::make('purchase_price')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->label('Precio de Compra'),

                        Forms\Components\TextInput::make('sale_price')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->label('Precio de Venta')
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $purchasePrice = $get('purchase_price');
                                        if ($value <= $purchasePrice) {
                                            $fail("El precio de venta no puede ser menor o igual al precio de compra.");
                                        }
                                    };
                                },
                            ]),
                    ])
                    ->collapsible(),

                // Sección: Fecha de Compra
                Forms\Components\Section::make('Fecha de Compra')
                    ->schema([
                        Forms\Components\DatePicker::make('purchase_date')
                            ->required()
                            ->label('Fecha de Compra')
                            ->default(now()),
                    ])
                    ->collapsible(),

                // Sección: Documento de Compra
                Forms\Components\Section::make('Documento de Compra')
                    ->schema([
                        Forms\Components\FileUpload::make('purchase_document_url')
                            ->label('Documento de Compra')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->required()
                            ->downloadable()
                            ->disk('public')
                            ->imagePreviewHeight('250') // Altura de la previsualización
                            ->dehydrated(false)
                            ->storeFiles() // Guarda el archivo en el disco antes de que se elimine el temporal
                            ->afterStateUpdated(function ($state, $set) {})
                            ->default(function ($record) use ($documentUrlService) {
                                // Si ya existe una imagen, generar la URL completa usando el servicio
                                if ($record && $record->purchase_document_url) {
                                    return $documentUrlService->getFullUrl($record->purchase_document_url);
                                }
                                return null; // Si no hay imagen, dejar el campo vacío
                            }),

                        // Botón flotante para ver o descargar el documento
                        Forms\Components\Actions::make([
                            Action::make('download_document')
                                ->label('Ver/Descargar Documento')
                                ->visible(fn ($record) => $record && $record->purchase_document_url)
                                ->url(fn ($record) => $record->purchase_document_url, true),
                        ]),
                    ])
                    ->extraAttributes(['style' => 'position: relative;'])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->limit(15)
                    ->tooltip(fn ($record) => $record->product?->name ?? 'N/A')
                    ->searchable(), // Habilitar búsqueda por nombre

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable(), // Si deseas permitir la búsqueda por proveedor

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(), // Habilitar búsqueda por SKU

                Tables\Columns\TextColumn::make('product.cva_key')
                    ->label('Clave CVA')
                    ->searchable(), // Habilitar búsqueda por clave CVA

                Tables\Columns\TextColumn::make('quantity')->label('Cantidad'),
                Tables\Columns\TextColumn::make('purchase_price')->label('Precio Compra')->money('MXN'),
                Tables\Columns\TextColumn::make('sale_price')->label('Precio Venta')->money('MXN'),
                Tables\Columns\TextColumn::make('purchase_date')->label('Fecha de Compra')->date(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}
