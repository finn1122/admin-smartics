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

        // Verifica si hay un nombre de archivo
        if (isset($data['temp_file_name'])) {
            $fileName = $data['temp_file_name'];
            Log::debug('Nombre del archivo: ' . $fileName);

            // Construye la ruta relativa del archivo en el disco
            $filePath = 'livewire-tmp/' . $fileName;
            Log::debug('Ruta relativa del archivo: ' . $filePath);

            // Verifica si el archivo existe en el disco
            if (Storage::disk('public')->exists($filePath)) {
                Log::debug('Archivo encontrado');

                // Obtén la ruta absoluta del archivo
                $tempFilePath = Storage::disk('public')->path($filePath);
                Log::debug('Ruta absoluta del archivo: ' . $tempFilePath);

                // Obtén el tipo MIME usando Storage
                $mimeType = Storage::disk('public')->mimeType($filePath);
                Log::debug('Tipo MIME del archivo: ' . $mimeType);

                // Crea un objeto UploadedFile a partir de la ruta temporal
                $file = new SymfonyUploadedFile(
                    $tempFilePath,
                    $fileName,
                    $mimeType,
                    UPLOAD_ERR_OK,
                    true
                );

                Log::debug('Objeto UploadedFile creado: ' . $file->getClientOriginalName());

                // Verifica si el archivo es válido
                if ($file->isValid()) {
                    Log::debug('El archivo es válido');

                    // Obtén el repositorio FTP
                    $ftpRepository = self::getFtpRepository();

                    // Guarda el archivo en el servidor FTP
                    $path = $ftpRepository->saveShopCategoryImage($record->id, $file);

                    // Actualiza el campo purchase_document_url con la nueva ruta
                    $record->image_url = $path;
                    $record->save();

                    // Elimina el archivo temporal si ya no es necesario
                    Storage::disk('public')->delete($filePath);
                    Log::debug('Archivo temporal eliminado: ' . $filePath);
                } else {
                    Log::error('El archivo no es válido: ' . $fileName);
                }
            } else {
                Log::error('El archivo temporal no existe en el disco: ' . $filePath);
            }
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
