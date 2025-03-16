<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopCategoryResource\Pages;
use App\Models\ShopCategory;
use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ShopCategoryResource\RelationManagers\ProductsRelationManager;


class ShopCategoryResource extends Resource
{
    protected static ?string $model = ShopCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationLabel(): string
    {
        return 'Categorias';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Categorias'; // Cambia "Lotes" por el nombre plural que desees
    }

    public static function getLabel(): ?string
    {
        return 'Categoria'; // Nombre en singular
    }
    protected static ?string $navigationGroup = 'Shop'; // Grupo en el menú


    public static function form(Form $form): Form
    {
        // Obtén una instancia del servicio DocumentUrlService
        $documentUrlService = app(DocumentUrlService::class);

        return $form
            ->schema([
                // Sección para la imagen y los toggles
                Forms\Components\Grid::make(1) // Una columna
                ->schema([
                    // Toggles (encima de la imagen)
                    Forms\Components\Grid::make(2) // Dos columnas para los toggles
                    ->schema([
                        Forms\Components\Toggle::make('top')
                            ->label('¿Está dentro del top?')
                            ->inline(false),

                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->inline(false)
                            ->default(true), // Establece un valor predeterminado
                    ])
                        ->columns(2), // Dos columnas para los toggles

                    // Imagen (debajo de los toggles)
                    Forms\Components\FileUpload::make('image_url')
                        ->label('Imagen')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(10240)
                        ->required()
                        ->downloadable()
                        ->disk('public')
                        ->imagePreviewHeight('250') // Altura de la previsualización
                        ->dehydrated(false)
                        ->storeFiles() // Guarda el archivo en el disco antes de que se elimine el temporal
                        ->afterStateUpdated(function ($state, $set) {})
                        ->default(function ($record) use ($documentUrlService) {
                            // Si ya existe una imagen, generar la URL completa usando el servicio
                            if ($record && $record->image_url) {
                                return $documentUrlService->getFullUrl($record->image_url);
                            }
                            return null; // Si no hay imagen, dejar el campo vacío
                        }),
                ]),

                // Sección para la información de la categoría
                Forms\Components\Section::make('Información de la categoría')
                    ->schema([
                        // Campo de nombre
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(), // Ocupa toda la fila

                        // Campo de descripción
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->nullable()
                            ->columnSpanFull(), // Ocupa toda la fila

                        // Campo de path
                        Forms\Components\TextInput::make('path')
                            ->label('Path')
                            ->helperText('Este campo se utiliza para construir la ruta (path) de la URL. Ejemplo: sillas-gamer')
                            ->nullable()
                            ->columnSpanFull(), // Ocupa toda la fila
                    ])
                    ->columns(1), // Una columna para esta sección
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna para el nombre
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                // Columna para la descripción
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),

                // Columna para la imagen
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen'),
                // Columna para el número de productos
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Número de Productos')
                    ->sortable(),

                // Columna para el estado "top"
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
                // Filtro para destacados
                Tables\Filters\Filter::make('top')
                    ->label('Solo destacados')
                    ->query(fn ($query) => $query->where('top', false)),

                // Filtro para activos
                Tables\Filters\Filter::make('active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('active', false)),
            ])
            ->actions([
                // Acción para editar
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Acciones masivas
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                // Cargar el contador de productos
                return $query->withCount('products');
            });
    }
    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class, // Registrar el RelationManagers aquí
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShopCategories::route('/'),
            'create' => Pages\CreateShopCategory::route('/create'),
            'edit' => Pages\EditShopCategory::route('/{record}/edit'),
        ];
    }
}
