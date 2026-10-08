<?php

namespace App\Http\Controllers;

use App\Enums\ParticipationStatus;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\City;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['city', 'sports']);
        $joined = $user->rooms()->wherePivot('status', ParticipationStatus::Joined->value);

        return view('profile.show', [
            'user' => $user,
            'stats' => [
                'upcoming' => (clone $joined)->where('match_date_time', '>=', now())->count(),
                'played' => (clone $joined)->where('match_date_time', '<', now())->count(),
                'organized' => $user->createdRooms()->count(),
            ],
        ]);
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('sports');

        return view('profile.edit', [
            'user' => $user,
            'cities' => City::orderBy('name')->get(),
            'sports' => Sport::orderBy('name')->get(),
            'mine' => $user->sports->keyBy('id'),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill(['name' => $data['name'], 'city_id' => $data['city_id'] ?? null]);

        $disk = Storage::disk('avatars');
        if ($request->hasFile('avatar') || $request->boolean('remove_avatar')) {
            if ($user->avatar_path) {
                $disk->delete($user->avatar_path);
            }
            $user->avatar_path = $request->hasFile('avatar') ? $request->file('avatar')->store('', 'avatars') : null;
        }
        $user->save();

        $user->sports()->sync(
            collect($data['sports'] ?? [])
                ->filter(fn ($s) => ! empty($s['level']))
                ->mapWithKeys(fn ($s, $sportId) => [(int) $sportId => [
                    'level' => $s['level'],
                    'position' => filled($s['position'] ?? null) ? trim($s['position']) : null,
                ]])
                ->all()
        );

        return redirect()->route('profile')->with('status', __('profile.flash.saved'));
    }
}
