<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CvaApiResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CvaApiResource extends Resource
{
    protected static ?string $navigationLabel = 'Actualizar todo'; // Nombre en el menú
    protected static ?string $navigationIcon = 'heroicon-o-cloud'; // Ícono en el menú
    protected static ?string $navigationGroup = 'CVA Api'; // Grupo en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
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

    public static function getNavigationItems(): array
    {
        return [
            'index' => NavigationItem::make('Actualizar todo')
                ->icon('heroicon-o-cloud')
                ->group('CVA Api')
                ->sort(1)
                ->url(static::getUrl('index')), // Agrega la URL para el ítem "Actualizar todo"
            'update-all-groups' => NavigationItem::make('Actualizar grupos')
                ->icon('heroicon-o-tag')
                ->group('CVA Api')
                ->sort(2)
                ->url(static::getUrl('update-all-groups')),
            'update-by-brand' => NavigationItem::make('Actualizar por marca')
                ->icon('heroicon-o-tag')
                ->group('CVA Api')
                ->sort(2)
                ->url(static::getUrl('update-by-brand')), // Agrega la URL para el ítem "Actualizar por marca"
        ];
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
            'index' => Pages\UpdateAllProducts::route('/'),
            'update-by-brand' => Pages\UpdateProductsByBrand::route('/update-by-brand'), // Nueva página
            'update-all-groups' => Pages\UpdateAllGroups::route('/update-all-groups'), // Nueva página

        ];
    }
}
