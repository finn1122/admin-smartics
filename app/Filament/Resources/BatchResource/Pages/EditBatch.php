<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Filament\Resources\BatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return 'Editar Lote'; // Cambia el título de la página "Edit"
    }

    protected function afterSave(): void
    {
        Log::debug('afterSave');

        // Accede al registro actualizado
        $record = $this->record;

        // Accede a los datos del formulario
        $data = $this->data;
        Log::debug($data);

        // Verifica si se ha cargado un nuevo archivo
        if (isset($data['purchase_document_url'])) {
            // Accede al nombre del archivo dentro del array
            $fileNames = $data['purchase_document_url'];
            $fileName = reset($fileNames); // Obtén el primer archivo del array

            // Verifica si el archivo ha cambiado
            if ($fileName !== $record->purchase_document_url) {
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

                // Guarda el archivo en el servidor FTP
                $ftpRepository = self::getFtpRepository();
                $path = $ftpRepository->saveBatchDocumentFile($record->id, $file);

                // Actualiza la URL del documento en el registro
                $record->purchase_document_url = $path;
                $record->save();

                Log::debug('Documento actualizado en FTP: ' . $path);
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
