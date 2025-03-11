<?php
namespace App\Filament\Resources\ShopCategoryResource\RelationManagers;

use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->searchable(),
                // Agrega más columnas si es necesario
            ])
            ->filters([
                // Agrega filtros si es necesario
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make() // Acción para asignar productos a la categoría
                ->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect() // Selecciona el producto
                    ->label('Producto')
                        ->required(),
                ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(), // Acción para remover un producto de la categoría
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(), // Acción masiva para remover productos
            ]);
    }
}
