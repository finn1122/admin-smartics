<?php
namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\BatchResource;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Log;

class GalleryRelationManager extends RelationManager
{
    protected static string $relationship = 'gallery';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                FileUpload::make('image_url')
                    ->label('Imagen')
                    ->image()
                    ->required()
                    ->afterStateUpdated(function ($state, $livewire) {
                        // Acceder al registro padre del producto
                        $product = $livewire->ownerRecord;

                        if (!$product) {
                            Log::error("No se pudo guardar la imagen porque el producto no existe aún.");
                            return;
                        }

                        // Obtener el repositorio FTP usando el método estático
                        $ftpRepository = BatchResource::getFtpRepository();

                        if ($state) {
                            $filePath = $ftpRepository->saveGalleryImage($product->id, $state);

                            Gallery::create([
                                'product_id' => $product->id,
                                'image_url' => $filePath,
                                'active' => true
                            ]);

                            Log::debug("Archivo guardado en FTP: {$filePath}");
                        }
                    }),

            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Imagen')
                    ->size(80),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
