<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'skill_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sports' => ['nullable', 'array'],
            'sports.*' => ['integer', 'exists:sports,id'],
            'sport_skill' => ['nullable', 'array'],
            'sport_skill.*' => ['integer', 'min:1', 'max:5'],
        ];
    }
}
