<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class MediaService
{
    public function __construct(
        private readonly Cloudinary $cloudinary
    ) {
    }

    /**
     * Upload image to Cloudinary.
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder,
        ?string $publicId = null
    ): array {

        $options = [
            'folder' => $folder,
            'overwrite' => true,
        ];

        if ($publicId) {
            $options['public_id'] = $publicId;
        }

        $upload = $this->cloudinary
            ->uploadApi()
            ->upload(
                $file->getRealPath(),
                $options
            );

        return [
            'public_id' => $upload['public_id'],
            'secure_url' => $upload['secure_url'],
        ];
    }

    /**
     * Delete image from Cloudinary.
     */
    public function deleteImage(
        ?string $publicId
    ): void {

        if (!$publicId) {
            return;
        }

        $this->cloudinary
            ->uploadApi()
            ->destroy($publicId);
    }
}
