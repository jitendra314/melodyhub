<?php

namespace App\Http\Requests\Profile;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('profiles', 'username')
                    ->ignore($this->user()->profile?->id),
            ],

            'bio' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],

            'gender' => [
                'nullable',
                new Enum(Gender::class),
            ],
        ];
    }
}
