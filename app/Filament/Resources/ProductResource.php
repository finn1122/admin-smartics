<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\GalleryRelationManager;
use App\Models\ExternalProductData;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getNavigationLabel(): string
    {
        return 'Productos';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Productos'; // Cambia "Lotes" por el nombre plural que desees
    }

    public static function getLabel(): ?string
    {
        return 'Producto'; // Nombre en singular
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

                // Campo para el código CVA
                Forms\Components\TextInput::make('cva_key')
                    ->label('Código CVA')
                    ->required()
                    ->maxLength(255),

                // Campo para el SKU
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255),

                // Campo para la garantía
                Forms\Components\TextInput::make('warranty')
                    ->label('Garantía')
                    ->required(),

                // Campo para el estado activo/inactivo
                Forms\Components\Toggle::make('active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            // Columnas estándar
            Tables\Columns\TextColumn::make('name')
                ->label('Nombre')
                ->limit(30)
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('cva_key')
                ->label('Código CVA')
                ->searchable(),

            Tables\Columns\TextColumn::make('sku')
                ->label('SKU')
                ->limit(16)
                ->searchable(),

            Tables\Columns\TextColumn::make('warranty')
                ->label('Garantía'),

            Tables\Columns\TextColumn::make('brand.name')
                ->label('Marca')
                ->sortable()
                ->searchable(),

            Tables\Columns\IconColumn::make('active')
                ->label('Activa')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle'),
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
                // Acción personalizada para administrar imágenes
                Tables\Actions\Action::make('manageGallery')
                    ->label('Administrar Imágenes')
                    ->url(function (Product $record): string {
                        Log::info('Generando URL para ManageGallery', ['product_id' => $record->id]);
                        return ProductResource::getUrl('gallery', ['record' => $record->id]);
                    })                    ->icon('heroicon-o-photo') // Icono para el botón
                    ->color('success'), // Color del botón
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
            GalleryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'gallery' => Pages\ManageGallery::route('/{record}/gallery'),
        ];
    }
}
