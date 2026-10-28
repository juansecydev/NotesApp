<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'createLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');

    Route::get('/register', [AuthController::class, 'createRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::post('/profile/avatar/uploads', [ProfileController::class, 'storeAvatarUpload'])
        ->name('profile.avatar.uploads.store');
    Route::delete('/profile/avatar/uploads', [ProfileController::class, 'revertAvatarUpload'])
        ->name('profile.avatar.uploads.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('notes', NoteController::class);
});
