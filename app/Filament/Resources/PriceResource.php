<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Filament\Resources\PriceResource\RelationManagers;
use App\Models\Batch;
use App\Models\ExternalProductData;
use App\Models\Price;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class PriceResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Precios';
    protected static ?string $navigationGroup = 'Gestión de Productos';

    public static function table(Table $table): Table
    {
        $columns = [
            // Columnas estándar
            Tables\Columns\TextColumn::make('name')
                ->label('Nombre')
                ->limit(30)
                ->sortable()
                ->searchable()
                ->tooltip(fn ($record) => $record->name),

            Tables\Columns\TextColumn::make('cva_key')
                ->label('Código CVA')
                ->searchable(),

            Tables\Columns\TextColumn::make('sku')
                ->label('SKU')
                ->limit(16)
                ->searchable(),

            Tables\Columns\TextColumn::make('brand.name')
                ->label('Marca')
                ->sortable()
                ->searchable(),
        ];

        // 🔹 Obtener todos los proveedores y crear columnas dinámicamente
        $suppliers = Supplier::all();
        foreach ($suppliers as $supplier) {
            $columns[] = Tables\Columns\TextColumn::make("supplier_{$supplier->id}")
                ->label($supplier->name)
                ->getStateUsing(fn ($record) =>
                    ExternalProductData::where('product_id', $record->id)
                        ->where('supplier_id', $supplier->id)
                        ->value('quantity') ?? '0' // Mostrar 0 si no hay registro
                );
        }

        // Agregar la columna de inventario (suma de cantidades de lotes)
        $columns[] = Tables\Columns\TextColumn::make('batches')
            ->label('Inventario')
            ->getStateUsing(fn ($record) =>
                $record->batches->sum('quantity') ?? '0' // Suma de la cantidad de los lotes
            );

        return $table
            ->columns($columns)
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(4)->schema([
                // 📌 Nombre del producto (Solo lectura)
                Forms\Components\TextInput::make('name')
                    ->label('Producto')
                    ->disabled(),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->disabled(),
                Forms\Components\TextInput::make('cva_key')
                    ->label('Clave CVA')
                    ->disabled(),
                ]),
                Forms\Components\Section::make('Precios por Proveedor')
                    ->schema(function ($record) {
                        $suppliers = Supplier::all();
                        $fields = [];

                        foreach ($suppliers as $supplier) {
                            // Obtener los datos del producto y proveedor
                            $externalProductData = ExternalProductData::where('product_id', $record->id)
                                ->where('supplier_id', $supplier->id)
                                ->first();

                            // Establecer valores predeterminados si no hay datos
                            $currencyCode = $externalProductData ? $externalProductData->currency_code : 'MXN';
                            $price = $externalProductData ? $externalProductData->price : 0;
                            $salePrice = $externalProductData ? $externalProductData->sale_price : 0;
                            $newSalePrice = $externalProductData ? $externalProductData->new_sale_price : 0;
                            $quantity = $externalProductData ? $externalProductData->quantity : 0;

                            Log::debug("ExternalProductData para {$supplier->name}: ", [
                                'currency_code' => $currencyCode,
                                'price' => $price,
                                'sale_price' => $salePrice,
                                'new_sale_price' => $newSalePrice,
                                'quantity' => $quantity,
                            ]);

                            // Agrupar campos por proveedor
                            $fields[] = Forms\Components\Section::make($supplier->name) // Título de la sección con el nombre del proveedor
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    // Cantidad disponible
                                    Forms\Components\TextInput::make("quantity_{$supplier->id}")
                                        ->label("Cantidad")
                                        ->formatStateUsing(fn () => $quantity)
                                        ->disabled(),

                                    // Moneda (Deshabilitado)
                                    Forms\Components\TextInput::make("currency_code_{$supplier->id}")
                                        ->label("Moneda")
                                        ->formatStateUsing(fn () => strtoupper($currencyCode))
                                        ->disabled(),

                                    // Precio de compra del proveedor (Deshabilitado)
                                    Forms\Components\TextInput::make("price_{$supplier->id}")
                                        ->label("Compra")
                                        ->numeric()
                                        ->required()
                                        ->default($price)
                                        ->formatStateUsing(fn () => $price)
                                        ->disabled(),

                                    // Precio de venta actual (Editable)
                                    Forms\Components\TextInput::make("supplier_{$supplier->id}_sale_price")
                                        ->label("Venta Actual")
                                        ->numeric()
                                        ->default($salePrice)
                                        ->formatStateUsing(fn () => $salePrice)
                                        ->rules([
                                            function ($get) use ($price) {
                                                return function (string $attribute, $value, $fail) use ($price) {
                                                    if ($value <= $price) {
                                                        $fail("El precio de venta no puede ser menor o igual al precio de compra.");
                                                    }
                                                };
                                            },
                                        ]),

                                    // Nuevo precio de venta (Editable)
                                    Forms\Components\TextInput::make("supplier_{$supplier->id}_new_sale_price")
                                        ->label("Nuevo Precio de Venta")
                                        ->numeric()
                                        ->default($newSalePrice)
                                        ->formatStateUsing(fn () => $newSalePrice)
                                        ->rules([
                                            function ($get) use ($price) {
                                                return function (string $attribute, $value, $fail) use ($price) {
                                                    if ($value <= $price) {
                                                        $fail("El nuevo precio de venta no puede ser menor o igual al precio de compra.");
                                                    }
                                                };
                                            },
                                        ]),
                                ]),
                            ]);
                        }

                        return $fields;
                    })
                    ->afterStateUpdated(function ($state, $get, $record) {
                        Log::debug('Estado actualizado: ', $state);

                        // Guardar los precios de venta de los proveedores
                        $suppliers = Supplier::all();

                        foreach ($suppliers as $supplier) {
                            $salePriceKey = "supplier_{$supplier->id}_sale_price";
                            $newSalePriceKey = "supplier_{$supplier->id}_new_sale_price";
                            $priceKey = "price_{$supplier->id}";

                            if (isset($state[$salePriceKey]) || isset($state[$newSalePriceKey])) {
                                $externalProductData = ExternalProductData::where('product_id', $record->id)
                                    ->where('supplier_id', $supplier->id)
                                    ->first();

                                $newSalePrice = $state[$newSalePriceKey] ?? ($externalProductData ? $externalProductData->new_sale_price : 0);
                                $salePrice = $state[$salePriceKey] ?? ($externalProductData ? $externalProductData->sale_price : 0);
                                $price = $state[$priceKey] ?? ($externalProductData ? $externalProductData->price : 0);

                                // Validar y guardar el nuevo precio de venta
                                if ($newSalePrice > $price) {
                                    if ($externalProductData) {
                                        $externalProductData->new_sale_price = $newSalePrice;
                                        $externalProductData->save();
                                        Log::debug("Proveedor: {$supplier->name} - Nuevo precio de venta actualizado a: {$externalProductData->new_sale_price}");
                                    }
                                } else {
                                    Notification::make()
                                        ->title('Error al actualizar el nuevo precio de venta')
                                        ->body("El nuevo precio de venta para el proveedor {$supplier->name} no es válido, ya que es menor que el precio de compra.")
                                        ->danger()
                                        ->send();
                                    return false;
                                }

                                // Validar y guardar el precio de venta actual
                                if ($salePrice > $price) {
                                    if ($externalProductData) {
                                        $externalProductData->sale_price = $salePrice;
                                        $externalProductData->save();
                                        Log::debug("Proveedor: {$supplier->name} - Precio de venta actualizado a: {$externalProductData->sale_price}");
                                    }
                                } else {
                                    Notification::make()
                                        ->title('Error al actualizar el precio de venta')
                                        ->body("El precio de venta para el proveedor {$supplier->name} no es válido, ya que es menor que el precio de compra.")
                                        ->danger()
                                        ->send();
                                    return false;
                                }
                            }
                        }
                    }),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrices::route('/'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
