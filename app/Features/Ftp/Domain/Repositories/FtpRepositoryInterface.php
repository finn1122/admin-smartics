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
    public function saveBatchDocumentFile($batchId, $file);
    public function deleteFileFromFtp($filePath);
    public function saveShopCategoryImage($categoryId, $file);
}
