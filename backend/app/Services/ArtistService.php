<?php

namespace App\Services;

use App\Models\Artist;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArtistService
{
    /**
     * Cloudinary folder for artist images.
     */
    private const IMAGE_FOLDER = 'artists/images';

    public function __construct(
        protected MediaService $mediaService,
        protected SlugService $slugService,
    ) {}

    /**
     * Get all artists.
     */
    public function getAll(
        int $perPage = 15
    ): LengthAwarePaginator {

        return Artist::query()

            ->latest()

            ->paginate($perPage);

    }

    /**
     * Get artist details.
     */
    public function getOne(
        Artist $artist
    ): Artist {
        return $artist;
    }

    /**
     * Create artist.
     */
    public function create(
        array $data,
        ?UploadedFile $image = null
    ): Artist {

        $upload = null;

        try {

            return DB::transaction(function () use ($data, $image, &$upload) {

                if ($image) {

                    $upload = $this->mediaService->uploadImage(
                        file: $image,
                        folder: self::IMAGE_FOLDER
                    );

                    $data['image_public_id'] = $upload['public_id'];
                }

                $data['slug'] = $this->slugService->generate(
                    Artist::class,
                    $data['name']
                );

                return Artist::create($data);

            });

        } catch (\Throwable $e) {

            if ($upload) {

                $this->mediaService->deleteImage(
                    $upload['public_id']
                );
            }

            throw $e;
        }
    }

    /**
     * Update artist.
     */
    public function update(
        Artist $artist,
        array $data,
        ?UploadedFile $image = null
    ): Artist {

        $newUpload = null;
        $oldPublicId = $artist->image_public_id;

        try {

            return DB::transaction(function () use (
                $artist,
                $data,
                $image,
                &$newUpload,
                $oldPublicId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Upload New Image
                |--------------------------------------------------------------------------
                */

                if ($image) {

                    $newUpload = $this->mediaService->uploadImage(
                        file: $image,
                        folder: self::IMAGE_FOLDER
                    );

                    $data['image_public_id'] = $newUpload['public_id'];
                }

                /*
                |--------------------------------------------------------------------------
                | Generate Slug
                |--------------------------------------------------------------------------
                */

                if ($artist->name !== $data['name']) {

                    $data['slug'] = $this->slugService->generate(
                        Artist::class,
                        $data['name'],
                        $artist->id
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Update Artist
                |--------------------------------------------------------------------------
                */

                $artist->update($data);

                /*
                |--------------------------------------------------------------------------
                | Delete Old Image
                |--------------------------------------------------------------------------
                */

                if ($newUpload && $oldPublicId) {

                    $this->mediaService->deleteImage(
                        $oldPublicId
                    );
                }

                return $artist->fresh();

            });

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Delete Newly Uploaded Image
            |--------------------------------------------------------------------------
            */

            if ($newUpload) {

                $this->mediaService->deleteImage(
                    $newUpload['public_id']
                );
            }

            throw $e;
        }
    }

    /**
     * Delete artist.
     */
    public function delete(
        Artist $artist
    ): void {

        DB::transaction(function () use ($artist) {

            /*
            |--------------------------------------------------------------------------
            | Delete Artist Image
            |--------------------------------------------------------------------------
            */

            if ($artist->image_public_id) {

                $this->mediaService->deleteImage(
                    $artist->image_public_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Soft Delete Artist
            |--------------------------------------------------------------------------
            */

            $artist->delete();

        });
    }
}
