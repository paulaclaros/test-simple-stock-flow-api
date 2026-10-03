<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

final class LocalStorageImageService implements ImageStorageServiceInterface
{
    public function storeImage(mixed $uploadedFile): string
    {
        $extension = 'jpg';
        if ($uploadedFile instanceof UploadedFile) {
            $extension = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
            $filename = Uuid::uuid4()->toString() . '.' . $extension;
            $uploadedFile->storeAs('public/media', $filename);
        } else {
            $filename = Uuid::uuid4()->toString() . '.jpg';
            Storage::put('public/media/' . $filename, (string) $uploadedFile);
        }

        return '/media/' . $filename;
    }
}
