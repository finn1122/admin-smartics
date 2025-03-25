<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource\RelationManagers;
use App\Models\ShopCategory;
use App\Models\Slider;
use App\Models\SliderType;
use App\Services\DocumentUrlService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shop'; // Grupo en el menú

    public static function form(Form $form): Form
    {
        // Obtén una instancia del servicio DocumentUrlService
        $documentUrlService = app(DocumentUrlService::class);

        return $form
            ->schema([
                Forms\Components\Section::make('Contenido Principal')
                    ->schema([
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

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('promo_message')
                            ->label('Mensaje Promocional')
                            ->maxLength(100)
                            ->helperText('Ej: "-30% OFF", "Nuevo Lanzamiento"'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración del Enlace')
                    ->schema([
                        Forms\Components\TextInput::make('button_text')
                            ->label('Texto del Botón')
                            ->default('Ver más')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('button_link')
                            ->label('URL de Destino')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Ej: /promociones, /nuevos-productos, https://externo.com')
                            ->url(fn ($operation) => $operation === 'edit')
                            ->prefix('https://')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuraciones Avanzadas')
                    ->schema([
                        Forms\Components\Select::make('slider_type_id')
                            ->label('Tipo de Slider')
                            ->options(SliderType::all()->pluck('display_name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio (opcional)')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\ColorPicker::make('bg_color')
                            ->label('Color de Fondo')
                            ->default('#ffffff'),

                        Forms\Components\Select::make('text_position')
                            ->label('Posición del Texto')
                            ->options([
                                'left' => 'Izquierda',
                                'right' => 'Derecha'
                            ])
                            ->default('left'),

                        Forms\Components\TextInput::make('order')
                            ->label('Orden de Visualización')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen')
                    ->size(80),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('button_link')
                    ->label('Enlace')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type.display_name')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Descuento' => 'danger',
                        'Oferta' => 'warning',
                        'Noticia' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('slider_type_id')
                    ->label('Tipo de Slider')
                    ->relationship('type', 'display_name'),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Solo activos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
