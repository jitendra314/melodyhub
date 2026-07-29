<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\MediaUrlService;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'profile' => [

                'username' => $this->profile?->username,

                'bio' => $this->profile?->bio,

                'avatar' => app(MediaUrlService::class)
                            ->image($this->profile?->avatar_public_id),

                'date_of_birth' => $this->profile?->date_of_birth,

                'gender' => $this->profile?->gender?->value,

            ],

        ];
    }
}
