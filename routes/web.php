<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SportController as AdminSportController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Public landing page with the sport selector and already-created rooms.
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/rooms', [LandingController::class, 'roomsPartial'])->name('landing.rooms');

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
});

// Admin area — requires an authenticated administrator.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/sports', [AdminSportController::class, 'store'])->name('sports.store');
    Route::delete('/sports/{sport}', [AdminSportController::class, 'destroy'])->name('sports.destroy');
});

require __DIR__.'/auth.php';
