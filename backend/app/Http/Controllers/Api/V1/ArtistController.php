<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests\Artist\StoreArtistRequest;
use App\Http\Requests\Artist\UpdateArtistRequest;
use App\Http\Resources\ArtistResource;
use App\Models\Artist;
use App\Services\ArtistService;
use Illuminate\Http\JsonResponse;

class ArtistController extends Controller
{
    public function __construct(
        protected ArtistService $artistService
    ) {}

    /**
     * Display artists.
     */
    public function index(
        Request $request
    ): JsonResponse {

        $artists = $this->artistService->getAll(

            perPage: $this->perPage($request)

        );

        return $this->successResponse(

            data: ArtistResource::collection(
                $artists
            )

        );
    }

    /**
     * Display artist.
     */
    public function show(
        Artist $artist
    ): JsonResponse {

        return $this->successResponse(
            data: new ArtistResource(
                $this->artistService->getOne($artist)
            )
        );
    }

    /**
     * Store artist.
     */
    public function store(
        StoreArtistRequest $request
    ): JsonResponse {

        $artist = $this->artistService->create(
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            data: new ArtistResource($artist),
            message: 'Artist created successfully.',
            status: 201
        );
    }

    /**
     * Update artist.
     */
    public function update(
        UpdateArtistRequest $request,
        Artist $artist
    ): JsonResponse {

        $artist = $this->artistService->update(
            $artist,
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            data: new ArtistResource($artist),
            message: 'Artist updated successfully.'
        );
    }

    /**
     * Delete artist.
     */
    public function destroy(
        Artist $artist
    ): JsonResponse {

        $this->artistService->delete($artist);

        return $this->successResponse(
            message: 'Artist deleted successfully.'
        );
    }
}
