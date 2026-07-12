<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlayerProfileRequest;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PlayerProfileController extends Controller
{
    /**
     * Show the player-profile form (bio, city, skill, sports played) — full Breeze page.
     */
    public function edit(Request $request): View
    {
        return view('profile.details', $this->formData($request->user()));
    }

    /**
     * Same form rendered as a slide-over partial for the unified landing surface.
     */
    public function editForm(Request $request): View
    {
        return view('landing.partials.profile-form', $this->formData($request->user()));
    }

    /**
     * Persist the player profile and sync the sports/skill pivot.
     *
     * AJAX (slide-over) → HTML partial (success marker or form with errors);
     * classic page → redirect back with a flash.
     */
    public function update(Request $request): Response|RedirectResponse
    {
        $rules = (new UpdatePlayerProfileRequest())->rules();
        $user = $request->user();

        if ($request->ajax()) {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->view('landing.partials.profile-form', array_merge(
                    $this->formData($user),
                    ['errorsBag' => $validator->errors(), 'values' => $request->all()],
                ), 422);
            }

            $this->save($user, $validator->validated());

            return response()->view('landing.partials.form-success', [
                'message' => __('landing.panel_profile_saved'),
            ]);
        }

        $this->save($user, $request->validate($rules));

        return redirect()
            ->route('profile.details.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $user): array
    {
        $user->load(['profile', 'sports']);

        return [
            'user' => $user,
            'profile' => $user->profile,
            'sports' => Sport::orderBy('name')->get(),
            'selectedSkill' => $user->sports->pluck('pivot.skill_level', 'id'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function save(User $user, array $data): void
    {
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

        $sync = [];
        foreach ($data['sports'] ?? [] as $sportId) {
            $sync[$sportId] = ['skill_level' => $data['sport_skill'][$sportId] ?? 1];
        }
        $user->sports()->sync($sync);
    }
}
