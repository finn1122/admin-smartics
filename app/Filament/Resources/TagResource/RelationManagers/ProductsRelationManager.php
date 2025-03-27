<?php

namespace App\Filament\Resources\TagResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // Muestra el campo 'name' como título
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags.name')
                    ->label('Etiquetas')
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->limitList(2) // Muestra máximo 2 etiquetas, el resto como "+X más"
                    ->searchable(), // Permite buscar por nombre de etiqueta
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Buscar producto')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search, ?array $state): array {
                                // Obtener IDs de productos ya asignados al tag actual
                                $assignedProductIds = $this->getOwnerRecord()
                                    ->products()
                                    ->pluck('products.id')
                                    ->toArray();

                                return \App\Models\Product::query()
                                    ->whereNotIn('id', $assignedProductIds) // Excluir productos ya asignados
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn ($product) => [
                                        $product->id => "{$product->name} (SKU: {$product->sku})"
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value) => \App\Models\Product::find($value)?->name),
                    ])
                    ->preloadRecordSelect()
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // Permite desvincular un producto
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(), // Desvincular múltiples productos
                ]),
            ]);
    }
}
