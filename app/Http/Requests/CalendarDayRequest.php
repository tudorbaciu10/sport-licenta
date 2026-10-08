<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** GET /calendar/day?city=chisinau&date=2026-10-12[&mine=1] */
class CalendarDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string', 'exists:cities,slug'],
            'date' => ['required', 'date_format:Y-m-d'],
            'mine' => ['nullable', 'boolean'],
        ];
    }
}
