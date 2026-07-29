<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug.
     *
     * @param class-string<Model> $modelClass
     */
    public function generate(
        string $modelClass,
        string $value,
        ?int $ignoreId = null
    ): string {

        $baseSlug = Str::slug($value);

        $slug = $baseSlug;

        $count = 2;

        /*
        |--------------------------------------------------------------------------
        | Include Soft Deleted Records
        |--------------------------------------------------------------------------
        */

        $query = in_array(
            SoftDeletes::class,
            class_uses_recursive($modelClass)
        )
            ? $modelClass::withTrashed()
            : $modelClass::query();

        while (
            (clone $query)
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
