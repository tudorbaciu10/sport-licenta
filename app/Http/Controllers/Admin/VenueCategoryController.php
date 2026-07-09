<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VenueCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VenueCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('venue_categories', 'name')],
            'icon' => ['nullable', 'string', 'max:16'],
        ]);

        VenueCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'icon' => $validated['icon'] ?? null,
        ]);

        return back()->with('status', 'Category added.');
    }

    public function destroy(VenueCategory $venueCategory): RedirectResponse
    {
        $venueCategory->delete();

        return back()->with('status', 'Category removed.');
    }
}
