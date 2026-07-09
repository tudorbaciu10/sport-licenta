<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlayerProfileRequest;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerProfileController extends Controller
{
    /**
     * Show the player-profile form (bio, city, skill, sports played).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['profile', 'sports']);

        return view('profile.details', [
            'user' => $user,
            'profile' => $user->profile,
            'sports' => Sport::orderBy('name')->get(),
            'selectedSkill' => $user->sports->pluck('pivot.skill_level', 'id'),
        ]);
    }

    /**
     * Persist the player profile and sync the sports/skill pivot.
     */
    public function update(UpdatePlayerProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $data['bio'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'skill_level' => $data['skill_level'] ?? 1,
            ],
        );

        // Build the sync payload: sport_id => ['skill_level' => n].
        $sync = [];
        foreach ($data['sports'] ?? [] as $sportId) {
            $sync[$sportId] = [
                'skill_level' => $data['sport_skill'][$sportId] ?? 1,
            ];
        }
        $user->sports()->sync($sync);

        return redirect()
            ->route('profile.details.edit')
            ->with('status', 'profile-updated');
    }
}
