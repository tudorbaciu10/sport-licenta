<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyMatchesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Middleware\SetLocale;
use App\Support\Consent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    session(['locale' => $locale]);

    // Remember the language beyond this session only if the visitor accepted preference cookies.
    if (Consent::preferences($request)) {
        // Not httpOnly: the cookie banner must be able to delete it when consent is withdrawn.
        Cookie::queue(Cookie::make(Consent::LOCALE_COOKIE, $locale, 60 * 24 * 365, httpOnly: false));
    }

    return back();
})->whereIn('locale', SetLocale::SUPPORTED)->name('locale');

// Legal pages linked from the cookie banner and the footer.
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');
Route::view('/confidentialitate', 'legal.privacy')->name('legal.privacy');

// Living design-system reference (tokens + components); also an annex for the thesis.
Route::view('/styleguide', 'styleguide')->name('styleguide');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

// JSON for the calendar (public; logged-in users get personal details too).
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/calendar/days', [CalendarController::class, 'days'])->name('calendar.days');
    Route::get('/calendar/day', [CalendarController::class, 'day'])->name('calendar.day');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::redirect('/dashboard', '/my-matches')->name('dashboard');
    Route::get('/my-matches', [MyMatchesController::class, 'index'])->name('my-matches');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('/rooms/{room}/interest', [RoomController::class, 'interest'])->name('rooms.interest');
    Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
});

// Declared after /rooms/create so "create" isn't captured as a room id.
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show')->whereNumber('room');
