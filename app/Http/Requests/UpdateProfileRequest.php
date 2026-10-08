<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            // sports[<sport id>][level|position]; a sport without a level is not saved.
            'sports' => ['nullable', 'array'],
            'sports.*.level' => ['nullable', Rule::in(User::LEVELS)],
            'sports.*.position' => ['nullable', 'string', 'max:60'],
        ];
    }
}
