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
                Forms\Components\Section::make('Precios por Lote')
                    ->schema(function ($record) {
                        if (!$record->id) {
                            Log::debug("El producto aún no tiene un ID asignado.");
                            return [];
                        }

                        $batches = Batch::where('product_id', $record->id)->get();

                        Log::debug("Lotes encontrados: " . $batches->count());

                        $fields = [];

                        foreach ($batches as $batch) {
                            Log::debug("Lote #{$batch->id} - Compra: {$batch->purchase_price} - Venta: {$batch->sale_price}");

                            $fields[] = Forms\Components\Grid::make(3)->schema([

                                // 📌 Moneda (Deshabilitado)
                                Forms\Components\TextInput::make("batch_currency_code")
                                    ->label("Compra (Lote #{$batch->id})")
                                    ->formatStateUsing(fn() => strtoupper('MXN')) // Mostrar 'MXN' en mayúsculas
                                    ->disabled(),

                                // 📌 Precio de compra del lote (Deshabilitado)
                                Forms\Components\TextInput::make("batch_{$batch->id}_price")
                                    ->label("Compra (Lote #{$batch->id})")
                                    ->numeric()
                                    ->disabled()
                                    ->formatStateUsing(fn() => $batch->purchase_price),

                                // 🔹 Precio de venta del lote (Editable)
                                Forms\Components\TextInput::make("batch_{$batch->id}_sale_price")
                                    ->label("Venta (Lote #{$batch->id})")
                                    ->numeric()
                                    ->default($batch->sale_price)
                                    ->formatStateUsing(fn() => $batch->sale_price)
                                    ->rules([
                                        function ($get) use ($batch) {
                                            return function (string $attribute, $value, $fail) use ($batch) {
                                                // Validar que el precio de venta no sea menor o igual al precio de compra
                                                if ($value <= $batch->purchase_price) {
                                                    $fail("El precio de venta del Lote #{$batch->id} no puede ser menor o igual al precio de compra.");
                                                }
                                            };
                                        },
                                    ]),
                            ]);
                        }

                        return $fields;
                    })
                    ->afterStateUpdated(function ($state, $get, $record) {
                        // Guardar los precios de venta de los lotes
                        $batches = Batch::where('product_id', $record->id)->get();

                        foreach ($batches as $batch) {
                            // Verificar si el precio de venta del lote fue actualizado
                            if (isset($state["batch_{$batch->id}_sale_price"])) {
                                $newSalePrice = $state["batch_{$batch->id}_sale_price"];
                                $purchasePrice = $batch->purchase_price;

                                // Validar que el precio de venta no sea menor o igual al precio de compra
                                if ($newSalePrice <= $purchasePrice) {
                                    // Mostrar un mensaje de error en el formulario
                                    Notification::make()
                                        ->title('Error al actualizar el precio de venta')
                                        ->body("El precio de venta del Lote #{$batch->id} no es válido, ya que es menor o igual que el precio de compra.")
                                        ->danger()
                                        ->send();

                                    // Retornar el estado para evitar la actualización del lote
                                    return false;
                                } else {
                                    // Si el precio es válido, actualizamos el lote
                                    $batch->sale_price = $newSalePrice;
                                    $batch->save();
                                    Log::debug("Lote #{$batch->id} - Precio de venta actualizado a: {$batch->sale_price}");
                                }
                            }
                        }
                    }),
                Forms\Components\Section::make('Precios por Proveedor')
                    ->schema(function ($record) {
                        $suppliers = Supplier::all();
                        $fields = [];

                        foreach ($suppliers as $supplier) {
                            // Buscar los datos del producto y proveedor
                            $externalProductData = ExternalProductData::where('product_id', $record->id)
                                ->where('supplier_id', $supplier->id)
                                ->first();

                            // Si no se encuentra el producto, establecer valores predeterminados
                            $currencyCode = $externalProductData ? $externalProductData->currency_code : '';
                            $price = $externalProductData ? $externalProductData->price : 0;
                            $salePrice = $externalProductData ? $externalProductData->sale_price : 0;
                            $quantity = $externalProductData ? $externalProductData->quantity : 0;

                            // Log para depuración
                            Log::debug("ExternalProductData para {$supplier->name}: ", [
                                'currency_code' => $currencyCode,
                                'price' => $price,
                                'sale_price' => $salePrice,
                            ]);

                            // Crear los campos de formulario
                            $fields[] = Forms\Components\Grid::make(4)->schema([

                                // Cantidad disponible
                                Forms\Components\TextInput::make("quantity")
                                    ->label("Cantidad ({$supplier->name})")
                                    ->formatStateUsing(fn() => $quantity)
                                    ->disabled(),

                                // Moneda (Deshabilitado)
                                Forms\Components\TextInput::make("currency_code")
                                    ->label("Moneda ({$supplier->name})")
                                    ->formatStateUsing(fn() => strtoupper($currencyCode))
                                    ->disabled(),

                                // Precio de compra del proveedor (Deshabilitado)
                                Forms\Components\TextInput::make("price")
                                    ->label("Compra ({$supplier->name})")
                                    ->numeric()
                                    ->required()
                                    ->default($price)
                                    ->readonly() // Cambiar a solo lectura para asegurar que se incluya en el estado
                                    ->formatStateUsing(fn() => $price),

                                // Precio de venta del proveedor (Editable)
                                Forms\Components\TextInput::make("supplier_{$supplier->id}_sale_price")
                                    ->label("Venta ({$supplier->name})")
                                    ->numeric()
                                    ->default($salePrice)
                                    ->formatStateUsing(fn() => $salePrice)
                                    ->rules([
                                        function ($get) use ($price) {
                                            return function (string $attribute, $value, $fail) use ($price) {
                                                // Validar que el precio de venta no sea menor o igual al precio de compra
                                                if ($value <= $price) {
                                                    $fail("El precio de venta no puede ser menor o igual al precio de compra.");
                                                }
                                            };
                                        },
                                    ]),
                            ]);
                        }

                        return $fields;
                    })
                    ->afterStateUpdated(function ($state, $get, $record) {
                        Log::debug('afterStateUpdated');

                        // Guardar los precios de venta de los proveedores
                        $suppliers = Supplier::all();

                        foreach ($suppliers as $supplier) {
                            // Buscar los datos del producto y proveedor
                            $externalProductData = ExternalProductData::where('product_id', $record->id)
                                ->where('supplier_id', $supplier->id)
                                ->first();

                            // Obtener el precio de compra desde el state o del valor predeterminado de la base de datos
                            $price = $state["supplier_{$supplier->id}_price"] ?? ($externalProductData ? $externalProductData->price : 0);

                            // Obtener el precio de venta (si no existe, poner el valor predeterminado o el de la base de datos)
                            $salePriceKey = "supplier_{$supplier->id}_sale_price";
                            $newSalePrice = $state[$salePriceKey] ?? ($externalProductData ? $externalProductData->sale_price : $price);

                            // Si el precio de venta es vacío o 0, establecerlo al precio de compra
                            if (empty($newSalePrice) || $newSalePrice == "0.00") {
                                $newSalePrice = $price; // Asignar el precio de compra si no se proporciona un precio de venta
                            }

                            Log::debug("Nuevo precio de venta: {$newSalePrice}, Precio de compra: {$price}");

                            if ($newSalePrice !== null && $price !== null) {
                                // Verificar que el sale_price no sea menor que el precio de compra
                                if ($newSalePrice >= $price) {
                                    if ($externalProductData) {
                                        // Solo actualizar si el precio ha cambiado
                                        if ($externalProductData->sale_price != $newSalePrice) {
                                            $externalProductData->sale_price = $newSalePrice;
                                            $externalProductData->save();

                                            // Log para depuración
                                            Log::debug("Proveedor: {$supplier->name} - Precio de venta actualizado a: {$externalProductData->sale_price}");
                                        }
                                    }
                                } else {
                                    // Si el precio de venta es menor que el precio de compra
                                    Log::warning("El precio de venta para el proveedor {$supplier->name} no es válido, ya que es menor que el precio de compra.");

                                    // Mostrar un mensaje de error en el formulario
                                    Notification::make()
                                        ->title('Error al actualizar el precio de venta')
                                        ->body("El precio de venta para el proveedor {$supplier->name} no es válido, ya que es menor que el precio de compra.")
                                        ->danger()
                                        ->send();

                                    // Retornar el estado para evitar la actualización del registro
                                    return false;
                                }
                            } else {
                                // Si no se encuentra alguno de los campos
                                Log::warning("No se encontró el precio de compra o venta para el proveedor {$supplier->name}");
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
