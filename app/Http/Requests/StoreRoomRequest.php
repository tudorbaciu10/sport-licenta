<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** The form sends rules as a list of inputs; drop the empty ones before validating. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Free unless a price is given; no collector when free.
            'price' => (int) $this->input('price', 0),
            'price_collector' => (int) $this->input('price', 0) > 0 ? $this->input('price_collector') : null,
            'rules' => array_values(array_filter(
                array_map(fn ($rule) => trim((string) $rule), (array) $this->input('rules', [])),
                fn ($rule) => $rule !== ''
            )),
        ]);
    }

    public function rules(): array
    {
        return [
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location_name' => ['required', 'string', 'max:160'],
            'venue_type' => ['nullable', Rule::in(array_keys(Room::VENUE_TYPES))],
            'match_date_time' => ['required', 'date', 'after:now'],
            'max_players' => ['required', 'integer', 'min:2', 'max:50'],
            'price' => ['integer', 'min:0', 'max:1000'],
            'price_collector' => ['nullable', 'required_unless:price,0', Rule::in(Room::PRICE_COLLECTORS)],
            'equipment_by' => ['nullable', Rule::in(Room::EQUIPMENT_BY)],
            'rules' => ['array', 'max:10'],
            'rules.*' => ['string', 'max:160'],
        ];
    }
}
