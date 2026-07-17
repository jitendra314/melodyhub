<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'], // hashed automatically by Laravel
            ]);

            $token = JWTAuth::fromUser($user);

            return [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ];
        });
    }
}
