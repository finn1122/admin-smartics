<?php

namespace App\Features\Ftp\Data\Repositories;

use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class FtpRepositoryImpl implements FtpRepositoryInterface
{
    // Definir una constante para la ruta del perfil de la panadería
    const BAKERY_PROFILE_PATH = 'bakery/%s/profile';
    const BRANCH_PROFILE_PATH = 'branch/%s/branch/%s/profile';

    /**
     * Guardar el archivo en el servidor FTP.
     *
     * @param string $directory
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function saveFileToFtp($directory, $file)
    {
        // Definir el nombre del archivo
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Ruta completa del archivo en el servidor FTP
        $filePath = $directory . '/' . $fileName;

        // Subir el archivo al servidor FTP
        Storage::disk('ftp')->put($filePath, fopen($file, 'r+'));

        return $filePath;
    }

    /**
     * Guardar la imagen de perfil de la panadería.
     *
     * @param int $bakeryId
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function saveBakeryProfileFile($bakeryId, $file)
    {
        // Directorio donde se almacenará la imagen de perfil de la panadería
        $directory = sprintf(self::BAKERY_PROFILE_PATH, $bakeryId);

        return $this->saveFileToFtp($directory, $file);
    }

    public function saveBranchProfileFile($bakeryId, $branchId, $file)
    {
        // Directorio donde se almacenará la imagen de perfil de la panadería
        $directory = sprintf(self::BAKERY_PROFILE_PATH, $bakeryId, $branchId);

        return $this->saveFileToFtp($directory, $file);
    }
}
