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
            'rules' => ['array', 'max:10'],
            'rules.*' => ['string', 'max:160'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sport_id' => 'sportul',
            'city_id' => 'orașul',
            'title' => 'titlul',
            'location_name' => 'locația',
            'venue_type' => 'tipul terenului',
            'match_date_time' => 'data și ora',
            'max_players' => 'numărul de jucători',
            'rules.*' => 'regula',
        ];
    }

    public function messages(): array
    {
        return [
            'match_date_time.after' => 'Meciul trebuie să fie în viitor.',
        ];
    }
}
