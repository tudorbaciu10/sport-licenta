<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** GET /calendar/days?city=chisinau&month=2026-10[&mine=1] */
class CalendarMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string', 'exists:cities,slug'],
            'month' => ['required', 'date_format:Y-m'],
            'mine' => ['nullable', 'boolean'],
        ];
    }
}
