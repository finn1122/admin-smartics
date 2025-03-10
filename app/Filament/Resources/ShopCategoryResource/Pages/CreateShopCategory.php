<?php

namespace App\Filament\Resources\ShopCategoryResource\Pages;

use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Filament\Resources\ShopCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class CreateShopCategory extends CreateRecord
{
    protected static string $resource = ShopCategoryResource::class;

    protected function afterCreate(): void
    {
        Log::debug('afterCreate');
        // Accede al registro recién creado
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
