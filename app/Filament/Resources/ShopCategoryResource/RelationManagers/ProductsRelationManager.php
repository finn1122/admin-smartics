<?php
namespace App\Filament\Resources\ShopCategoryResource\RelationManagers;

use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) =>
                            \App\Models\Product::where('name', 'like', "%{$query}%")
                                ->pluck('name', 'id')
                            )
                            ->getOptionLabelUsing(fn ($value) => \App\Models\Product::find($value)?->name)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('imageUrl', \App\Models\Product::find($state)?->gallery()->first()?->image_url)
                            )
                            ->required(),

                        Forms\Components\Placeholder::make('image_preview')
                            ->label('Vista previa de la imagen')
                            ->content(fn ($get) => new HtmlString(
                                $get('imageUrl')
                                    ? "<img src='{$get('imageUrl')}' style='max-width: 150px; border-radius: 5px;' />"
                                    : '<span style="color: gray;">No hay imagen disponible</span>'
                            )),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }

}
