<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * GET /rooms/map — the same filters as /rooms (RoomController::index) except the city:
 * on the map the visible area is the place filter, so a city in the query is ignored.
 */
class RoomMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sport' => ['nullable', 'string', 'exists:sports,slug'],
            'date' => ['nullable', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', Rule::in(array_keys(Room::VENUE_TYPES))],
            'free' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:80'],
            'when' => ['nullable', Rule::in(['weekend'])],
            'price' => ['nullable', Rule::in(['free'])],
        ];
    }
}
