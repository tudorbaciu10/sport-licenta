<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SportController as AdminSportController;
use App\Http\Controllers\Admin\VenueCategoryController as AdminVenueCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Public landing page with the sport selector, rooms, and the facilities marketplace.
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/rooms', [LandingController::class, 'roomsPartial'])->name('landing.rooms');
Route::get('/facilities', [LandingController::class, 'venuesPartial'])->name('landing.venues');

// Switch the interface language (ro / en / ru).
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang.switch');

Route::get('/dashboard', function () {
    $user = request()->user();

    return view('dashboard', [
        'createdEvents' => $user->createdEvents()
            ->with('sport')
            ->withCount('participants')
            ->orderBy('start_time')
            ->get(),
        'joinedEvents' => $user->events()
            ->with('sport')
            ->withCount('participants')
            ->orderBy('start_time')
            ->get(),
        'myVenues' => $user->venues()->with('category')->latest()->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Account settings (name / email / password / delete) — provided by Breeze.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Player profile (bio / city / skill / sports played).
    Route::get('/profile/details', [PlayerProfileController::class, 'edit'])->name('profile.details.edit');
    Route::patch('/profile/details', [PlayerProfileController::class, 'update'])->name('profile.details.update');

    // Events.
    Route::resource('events', EventController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/events/{event}/join', [EventParticipationController::class, 'store'])->name('events.join');
    Route::delete('/events/{event}/leave', [EventParticipationController::class, 'destroy'])->name('events.leave');

    // Facility management (owner). "create" is registered before the public {venue} route below.
    Route::get('/my/facilities', [VenueController::class, 'mine'])->name('venues.mine');
    Route::get('/facilities/create', [VenueController::class, 'create'])->name('venues.create');
    Route::post('/facilities', [VenueController::class, 'store'])->name('venues.store');
    Route::get('/facilities/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::patch('/facilities/{venue}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/facilities/{venue}', [VenueController::class, 'destroy'])->name('venues.destroy');
});

// Public facility page (registered after /facilities/create so it doesn't swallow it).
Route::get('/facilities/{venue}', [VenueController::class, 'show'])->name('venues.show');

// Admin area — requires an authenticated administrator.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/sports', [AdminSportController::class, 'store'])->name('sports.store');
    Route::delete('/sports/{sport}', [AdminSportController::class, 'destroy'])->name('sports.destroy');
    Route::post('/venue-categories', [AdminVenueCategoryController::class, 'store'])->name('venue-categories.store');
    Route::delete('/venue-categories/{venueCategory}', [AdminVenueCategoryController::class, 'destroy'])->name('venue-categories.destroy');
});

require __DIR__.'/auth.php';
