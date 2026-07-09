<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'venue_category_id' => ['required', 'integer', 'exists:venue_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'surface' => ['nullable', 'string', 'max:50'],
            'is_indoor' => ['nullable', 'boolean'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'price_per_hour' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_indoor' => $this->boolean('is_indoor'),
        ]);
    }
}
