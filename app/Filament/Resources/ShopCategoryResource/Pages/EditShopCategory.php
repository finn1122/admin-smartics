<?php

namespace App\Filament\Resources\ShopCategoryResource\Pages;

use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Filament\Resources\ShopCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class EditShopCategory extends EditRecord
{
    protected static string $resource = ShopCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // El método afterSave se ejecutará después de que se haya guardado el registro
    protected function afterSave(): void
    {
        Log::debug('afterSave - Editando la categoría de tienda');

        // Accede al registro editado
        $record = $this->record;

        // Accede a los datos del formulario
        $data = $this->data;

        Log::debug($data);

        // Verifica si hay un archivo cargado
        if (isset($data['image_url'])) {
            // Accede al nombre del archivo dentro del array
            $fileNames = $data['image_url'];
            $fileName = reset($fileNames); // Obtén el primer archivo del array

            // Obtén la ruta del archivo en el disco
            $filePath = Storage::disk('public')->path($fileName);

            // Obtén el tipo MIME del archivo
            $mimeType = Storage::disk('public')->mimeType($fileName);

            // Crea un objeto SymfonyUploadedFile
            $file = new SymfonyUploadedFile(
                $filePath,
                $fileName,
                $mimeType,
                UPLOAD_ERR_OK,
                true
            );

            // También puedes subir el archivo a un servidor FTP
            $ftpRepository = self::getFtpRepository();
            $path = $ftpRepository->saveShopCategoryImage($record->id, $file);

            // Actualiza la URL de la imagen en el registro
            $record->image_url = $path;
            $record->save();

            Log::debug($path);
        } else {
            Log::error('No se encontró el nombre del archivo en los datos del formulario.');
        }
    }

    // Método estático para obtener el repositorio FTP
    public static function getFtpRepository()
    {
        // Asegúrate de que estás instanciando correctamente el repositorio FTP
        return app(FtpRepositoryInterface::class);
    }
}
