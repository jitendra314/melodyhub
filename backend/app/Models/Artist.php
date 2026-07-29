<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'image_public_id',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * Route model binding by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
