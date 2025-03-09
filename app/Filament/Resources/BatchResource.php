<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages;
use App\Models\Batch;
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
        return $form
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

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->label('Cantidad'),

                Forms\Components\DatePicker::make('purchase_date')
                    ->required()
                    ->label('Fecha de Compra')
                    ->default(now()),

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

                // Contenedor flexible para el botón y el input de archivo
                Forms\Components\Group::make([
                    // Botón flotante en la parte superior derecha
                    Forms\Components\Actions::make([
                        Action::make('download_document')
                            ->label('Ver/Descargar Documento')
                            ->visible(fn ($record) => $record && $record->purchase_document_url)
                            ->url(fn ($record) => $record->purchase_document_url, true),
                    ]),
                    Forms\Components\Section::make('')
                        ->schema([
                            Forms\Components\FileUpload::make('purchase_document_url')
                                ->label('Documento de Compra')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->maxSize(10240)
                                ->required()
                                ->downloadable()
                                ->disk('public')
                                ->directory('livewire-tmp')
                                ->dehydrated(false)
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $set('temp_file_path', $state->getPathname());
                                        $set('temp_file_name', $state->getClientOriginalName());
                                    }
                                }),
                        ])
                        ->extraAttributes(['style' => 'position: relative;']), // Permite posicionar el botón
                ]),
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
