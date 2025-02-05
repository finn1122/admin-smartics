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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class PriceResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Precios';
    protected static ?string $navigationGroup = 'Gestión de Productos';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Producto')->searchable()->sortable(),
            ])
            ->filters([
                // Filtros opcionales
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Acción para editar precios
            ])
            ->bulkActions([
                // Acciones masivas opcionales
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 📌 Nombre del producto (Solo lectura)
                Forms\Components\TextInput::make('name')
                    ->label('Producto')
                    ->disabled(),

                // 🔹 Sección para precios de proveedores (En una fila)
                Forms\Components\Section::make('Precios por Proveedor')
                    ->schema(function ($record) {
                        if (!$record->id) {
                            Log::debug("El producto aún no tiene un ID asignado.");
                            return [];
                        }

                        $suppliers = Supplier::all();
                        $externalProductData = ExternalProductData::where('product_id', $record->id)->get()->keyBy('supplier_id');

                        Log::debug("Número de proveedores: " . $suppliers->count());

                        $fields = [];

                        foreach ($suppliers as $supplier) {
                            $supplierData = $externalProductData[$supplier->id] ?? null;

                            Log::debug("Proveedor: {$supplier->name} - Compra: " . ($supplierData?->price ?? 'N/A') . " - Venta: " . ($supplierData?->sale_price ?? 'N/A'));

                            $fields[] = Forms\Components\Grid::make(2)->schema([
                                // 📌 Precio de compra del proveedor (Deshabilitado)
                                Forms\Components\TextInput::make("supplier_{$supplier->id}_price")
                                    ->label("Compra ({$supplier->name})")
                                    ->numeric()
                                    ->disabled()
                                    ->formatStateUsing(fn() => $supplierData?->price ?? 0),

                                // 🔹 Precio de venta del proveedor (Editable)
                                Forms\Components\TextInput::make("supplier_{$supplier->id}_sale_price")
                                    ->label("Venta ({$supplier->name})")
                                    ->numeric()
                                    ->formatStateUsing(fn() => $supplierData?->sale_price ?? 0),
                            ]);
                        }

                        return $fields;
                    }),



                // 🔹 Sección para precios de lotes (En una fila)
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

                            $fields[] = Forms\Components\Grid::make(2)->schema([
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
                                    ->formatStateUsing(fn() => $batch->sale_price),
                            ]);
                        }

                        return $fields;
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
