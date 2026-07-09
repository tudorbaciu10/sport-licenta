<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SportController extends Controller
{
    /**
     * Add a new sport to the platform's reference list.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sports', 'name')],
        ]);

        Sport::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('status', 'Sport added.');
    }

    /**
     * Remove a sport (its events cascade-delete via the FK).
     */
    public function destroy(Sport $sport): RedirectResponse
    {
        $sport->delete();

        return back()->with('status', 'Sport removed.');
    }
}
