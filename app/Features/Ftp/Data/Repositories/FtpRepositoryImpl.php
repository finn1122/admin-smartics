<?php

namespace App\Features\Ftp\Data\Repositories;

use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FtpRepositoryImpl implements FtpRepositoryInterface
{
    // Definir una constante para la ruta del perfil de la panadería
    const PRODUCT_GALLERY_PATH = 'product/%s/gallery';
    const BATCH_DOCUMENTS_PATH = 'batch/%s/documents';

    /**
     * Guardar el archivo en el servidor FTP.
     *
     * @param string $directory
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function saveFileToFtp($directory, $file)
    {
        Log::info('Iniciando saveFileToFtp');

        // Validar que el archivo sea válido
        if (!$file->isValid()) {
            Log::error('El archivo no es válido.', ['file' => $file->getClientOriginalName()]);
            throw new \Exception('El archivo no es válido.');
        }

        // Definir el nombre del archivo
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Ruta completa del archivo en el servidor FTP
        $filePath = $directory . '/' . $fileName;

        try {
            // Abrir el archivo en modo lectura
            $fileResource = fopen($file->getPathname(), 'r');
            if (!$fileResource) {
                Log::error('No se pudo abrir el archivo para lectura.', ['path' => $file->getPathname()]);
                throw new \Exception('No se pudo abrir el archivo para lectura.');
            }

            // Subir el archivo al servidor FTP
            $uploadResult = Storage::disk('ftp')->put($filePath, $fileResource);

            if ($uploadResult) {
                Log::info('Archivo subido exitosamente.', ['filePath' => $filePath]);
            } else {
                Log::error('Error al subir el archivo al servidor FTP.', ['filePath' => $filePath]);
                throw new \Exception('Error al subir el archivo al servidor FTP.');
            }

            // Cerrar el recurso del archivo
            fclose($fileResource);
        } catch (\Exception $e) {
            Log::error('Excepción en saveFileToFtp:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e; // Relanzar la excepción para manejarla en un nivel superior
        }

        return $filePath;
    }


    public function saveBatchDocumentFile($batchId, $file)
    {
        Log::info('saveBatchDocumentFile');
        // Directorio donde se almacenará la imagen de perfil de la panadería
        $directory = sprintf(self::BATCH_DOCUMENTS_PATH, $batchId);
        Log::debug($directory);

        return $this->saveFileToFtp($directory, $file);
    }
    public function saveGalleryImage($productId, $image)
    {
        Log::info('saveGalleryImage');
        // Directorio donde se almacenará la imagen de perfil de la panadería
        $directory = sprintf(self::PRODUCT_GALLERY_PATH, $productId);

        return $this->saveFileToFtp($directory, $image);
    }
}
