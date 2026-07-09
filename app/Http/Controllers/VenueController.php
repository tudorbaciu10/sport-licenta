<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VenueController extends Controller
{
    /**
     * The current user's listed facilities.
     */
    public function mine(Request $request): View
    {
        return view('venues.mine', [
            'venues' => $request->user()->venues()->with('category')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('venues.create', [
            'categories' => VenueCategory::orderBy('name')->get(),
        ]);
    }

    public function store(StoreVenueRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $data['source'] = 'user';
        $data['is_published'] = true;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('venues', 'public');
        }

        $venue = $request->user()->venues()->create($data);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', __('venues.created'));
    }

    /**
     * Public facility page.
     */
    public function show(Venue $venue): View
    {
        abort_unless($venue->is_published || $this->canManage($venue), 404);

        $venue->load(['category', 'owner']);

        return view('venues.show', [
            'venue' => $venue,
        ]);
    }

    public function edit(Venue $venue): View
    {
        $this->authorize('update', $venue);

        return view('venues.edit', [
            'venue' => $venue,
            'categories' => VenueCategory::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateVenueRequest $request, Venue $venue): RedirectResponse
    {
        $this->authorize('update', $venue);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($venue->photo_path) {
                Storage::disk('public')->delete($venue->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('venues', 'public');
        }

        $venue->update($data);

        return redirect()
            ->route('venues.show', $venue)
            ->with('status', __('venues.updated'));
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorize('delete', $venue);

        if ($venue->photo_path) {
            Storage::disk('public')->delete($venue->photo_path);
        }

        $venue->delete();

        return redirect()
            ->route('venues.mine')
            ->with('status', __('venues.deleted'));
    }

    private function canManage(Venue $venue): bool
    {
        $user = request()->user();

        return $user !== null && ($user->id === $venue->user_id || $user->isAdmin());
    }
}
