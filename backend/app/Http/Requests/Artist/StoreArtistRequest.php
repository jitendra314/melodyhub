<?php

namespace App\Http\Requests\Artist;

use Illuminate\Foundation\Http\FormRequest;

class StoreArtistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'verified' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
