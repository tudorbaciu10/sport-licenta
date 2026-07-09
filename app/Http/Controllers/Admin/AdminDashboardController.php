<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin overview: platform stats + reference-data management.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'admins' => User::where('role', User::ROLE_ADMIN)->count(),
                'events' => Event::count(),
                'upcomingEvents' => Event::upcoming()->count(),
                'sports' => Sport::count(),
                'venues' => Venue::count(),
            ],
            'sports' => Sport::withCount('events')->orderBy('name')->get(),
            'recentEvents' => Event::with(['sport', 'creator'])->latest()->take(5)->get(),
        ]);
    }
}
