<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home - redirect sesuai status login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('member.dashboard');
    }
    return redirect()->route('member.login');
});

// ─── Guest Only (belum login) ───
Route::middleware('guest')->group(function () {
    Route::get('/member/register', [RegisterController::class, 'showRegistrationForm'])->name('member.register');
    Route::post('/member/register', [RegisterController::class, 'register'])->name('member.register.submit')->middleware('throttle:5,5');

    Route::get('/member/login', [LoginController::class, 'showLoginForm'])->name('member.login');
    Route::post('/member/login', [LoginController::class, 'login'])->name('member.login.submit')->middleware('throttle:5,5');
});

// ─── Authenticated + Role Member ───
Route::middleware(['auth', 'role:member'])->prefix('member')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('member.dashboard');
});

// ─── Logout (authenticated) ───
Route::post('/member/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('member.logout');
