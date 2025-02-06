<?php

/**
 * Interface for managing file operations on an FTP server.
 */

namespace App\Features\Ftp\Domain\Repositories;

interface FtpRepositoryInterface
{
    /**
     * Guardar el archivo en el servidor FTP.
     *
     * @param string $directory
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function saveFileToFtp($directory, $file);

    /**
     * Guardar la imagen de perfil de la panadería.
     *
     * @param int $bakeryId
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function saveBakeryProfileFile($bakeryId, $file);

    /**
     * Save the profile file for a specific bakery branch.
     *
     * @param int $bakeryId Unique identifier of the bakery.
     * @param int $branchId Unique identifier of the branch.
     * @param \Illuminate\Http\UploadedFile $file The profile file to be saved.
     * @return bool Indicates whether the file was successfully saved.
     */
    public function saveBranchProfileFile($bakeryId, $branchId, $file);
}
