<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'city' => ['nullable', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:2', 'max:1000'],
            'skill_level' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
