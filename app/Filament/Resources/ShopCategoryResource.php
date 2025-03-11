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
                // Campo de carga de imagen con previsualización
                Forms\Components\Grid::make(2) // Dos columnas
                ->schema([
                    // Columna para la imagen
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

                    // Columna para los toggles (esquina superior derecha)
                    Forms\Components\Grid::make(2) // Una columna
                    ->schema([
                        Forms\Components\Toggle::make('top')
                            ->label('¿Está dentro del top?')
                            ->inline(false),

                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->inline(true),
                    ])
                        ->columnSpan(1), // Ocupa una columna
                ])
                    ->columns(2), // Dos columnas en total
                Forms\Components\Grid::make(2)
                    ->schema([
                    // Campo de nombre (fila independiente)
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                    ]),
                    // Campo de descripción (fila independiente)
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->nullable(),
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
                Tables\Columns\ImageColumn::make('imageUrl')
                    ->label('Imagen'),

                // Columna para el estado "top"
                Tables\Columns\IconColumn::make('top')
                    ->label('¿esta dentro del top?')
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
            ]);
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
