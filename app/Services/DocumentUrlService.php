<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DocumentUrlService
{
    /**
     * Genera la URL completa para un archivo.
     */
    public function getFullUrl($filePath)
    {
        Log::info('getFullUrl');
        // Si no hay un valor, devolver null
        if (empty($filePath)) {
            return null;
        }

        // Obtener la URL base del servidor FTP desde .env
        $ftpServerUrl = env('FTP_SERVER_URL', 'http://localhost/store-ftp');

        // Concatenar la URL base con la ruta del archivo
        return $ftpServerUrl . '/' . ltrim($filePath, '/');
    }
}
