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
            // Social handles (not URLs): the links are built on the server, see User::socialLinks().
            'telegram' => ['nullable', 'regex:/^[A-Za-z0-9_]{5,32}$/'],
            'instagram' => ['nullable', 'regex:/^[A-Za-z0-9._]{1,30}$/'],
            'facebook' => ['nullable', 'regex:/^[A-Za-z0-9.]{5,50}$/'],
            'whatsapp' => ['nullable', 'regex:/^\+?[0-9]{8,15}$/'],
        ];
    }

    public function messages(): array
    {
        return collect(['telegram', 'instagram', 'facebook', 'whatsapp'])
            ->mapWithKeys(fn ($n) => [$n.'.regex' => __('profile.social_invalid.'.$n)])
            ->all();
    }

    /** Accept what people paste: "@ion", "t.me/ion", "https://instagram.com/ion/", "+373 69 123 456". */
    protected function prepareForValidation(): void
    {
        $clean = function (?string $value, string $hosts): ?string {
            $value = trim((string) $value);
            $value = preg_replace('#^(https?://)?(www\.|m\.)?('.$hosts.')/#i', '', $value);
            $value = trim(strtok($value, '?#') ?: '', "/@ \t");

            return $value === '' ? null : $value;
        };

        $this->merge([
            'telegram' => $clean($this->input('telegram'), 't\.me|telegram\.me'),
            'instagram' => $clean($this->input('instagram'), 'instagram\.com'),
            'facebook' => $clean($this->input('facebook'), 'facebook\.com|fb\.com'),
            'whatsapp' => ($w = preg_replace('/[\s\-().]/', '', (string) $this->input('whatsapp'))) === '' ? null : $w,
        ]);
    }
}
