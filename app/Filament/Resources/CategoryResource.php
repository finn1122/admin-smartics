<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Shop'; // Grupo en el menú

    protected static ?string $navigationLabel = 'Categorías'; // Etiqueta de navegación

    // Se puede personalizar el icono de la categoría en el menú
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // ¿Queremos que la opción esté visible en el menú?
    protected static ?bool $navigationVisible = true;

    // Orden del recurso en el menú
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para el nombre de la categoría
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                // Campo para el parent_id (categoría principal)
                Forms\Components\Select::make('parent_id')
                    ->label('Categoría Principal')
                    ->options(function () {
                        return Category::all()->pluck('name', 'id')->toArray();
                    })
                    ->nullable()
                    ->searchable()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Asegúrate de que $state sea la instancia del modelo Category y no un string
                        $category = $state; // Esto debe ser la instancia del modelo, no solo el nombre
                        if ($category instanceof Category) {
                            // Usar los ancestros para calcular la jerarquía con indentación
                            $indentation = str_repeat('—', $category->ancestors()->count());
                            return $indentation . ' ' . $category->name;
                        }
                        return $state; // Si no es una instancia de Category, solo retorna el estado original
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoría superior')
                    ->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    // Define los filtros
    public static function getGlobalSearchQuery(): Builder
    {
        return Category::query();
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
