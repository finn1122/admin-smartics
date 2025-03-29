<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use App\Services\DocumentUrlService;
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
        // Obtén una instancia del servicio DocumentUrlService
        $documentUrlService = app(DocumentUrlService::class);

        return $form
            ->schema([
                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('top')
                            ->label('¿Está dentro del top?')
                            ->inline(false),

                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->inline(false)
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Imagen')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Imagen')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(10240)
                            ->required()
                            ->downloadable()
                            ->disk('public')
                            ->imagePreviewHeight('250')
                            ->dehydrated(false)
                            ->storeFiles()
                            ->afterStateUpdated(function ($state, $set) {})
                            ->default(function ($record) use ($documentUrlService) {
                                if ($record && $record->image_url) {
                                    return $documentUrlService->getFullUrl($record->image_url);
                                }
                                return null;
                            }),
                    ]),

                Forms\Components\Section::make('Información de la categoría')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('parent_id')
                            ->label('Categoría Principal')
                            ->options(function () {
                                return Category::all()->pluck('name', 'id')->toArray();
                            })
                            ->nullable()
                            ->searchable()
                            ->default(null),
                        Forms\Components\TextInput::make('path')
                            ->label('Ruta')
                            ->required()
                            ->maxLength(255),
                    ]),
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
                // Columna para la imagen
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen'),
                // Columna para el número de productos
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Número de Productos')
                    ->sortable(),
                Tables\Columns\IconColumn::make('top')
                    ->label('¿es top?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                /// Filtro para destacados
                Tables\Filters\Filter::make('top')
                    ->label('Solo destacados')
                    ->query(fn ($query) => $query->where('top', false)),

                // Filtro para activos
                Tables\Filters\Filter::make('active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('active', false)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(function ($query) {
                // Cargar el contador de productos
                return $query->withCount('products');
            });
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
