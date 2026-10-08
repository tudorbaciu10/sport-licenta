<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/locale/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back();
})->whereIn('locale', SetLocale::SUPPORTED)->name('locale');

// Living design-system reference (tokens + components); also an annex for the thesis.
Route::view('/styleguide', 'styleguide')->name('styleguide');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::redirect('/dashboard', '/rooms')->name('dashboard');

    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('/rooms/{room}/interest', [RoomController::class, 'interest'])->name('rooms.interest');
    Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
});

// Declared after /rooms/create so "create" isn't captured as a room id.
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show')->whereNumber('room');
