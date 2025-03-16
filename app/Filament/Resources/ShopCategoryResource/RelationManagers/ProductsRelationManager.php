<?php
namespace App\Filament\Resources\ShopCategoryResource\RelationManagers;

use App\Models\Product;
use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products'; // Nombre de la relación en el modelo ShopCategory

    protected static ?string $recordTitleAttribute = 'name'; // Columna que se mostrará como título del registro

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                // Agrega más campos si es necesario
            ]);
    }

    public function table(Table $table): Table
    {
        $documentUrlService = app(DocumentUrlService::class);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->copyable()
                    ->searchable(),
            ])

            ->filters([
                // Agrega filtros si es necesario
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Producto')
                            ->required()
                            ->options(function () {
                                // Obtén los productos que no están asociados a la categoría actual
                                $existingProductIds = $this->getOwnerRecord()->products->pluck('id');
                                return \App\Models\Product::whereNotIn('id', $existingProductIds)
                                    ->pluck('name', 'id');
                            }),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('manageGallery')
                    ->label('Galería')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->modalContent(
                        fn (Product $record) =>
                        view('livewire.gallery-modal', ['product' => $record]))
                    ->modalHeading('Galería de Imágenes'),

                Tables\Actions\DetachAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
