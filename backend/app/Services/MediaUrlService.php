<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class MediaUrlService
{
    public function __construct(
        protected Cloudinary $cloudinary
    ) {}

    /**
     * Generate secure Cloudinary URL.
     */
    public function image(
        ?string $publicId
    ): ?string {

        if (! $publicId) {
            return null;
        }

        return $this->cloudinary
            ->image($publicId)
            ->toUrl();
    }
}
