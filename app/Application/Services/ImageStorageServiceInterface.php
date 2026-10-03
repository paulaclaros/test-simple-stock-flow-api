<?php

declare(strict_types=1);

namespace App\Application\Services;

interface ImageStorageServiceInterface
{
    /**
     * @param mixed $uploadedFile Archivo subido (UploadedFile de Laravel o stream)
     * @return string URL pública relativa ej: "/media/abc123.jpg"
     */
    public function storeImage(mixed $uploadedFile): string;
}
