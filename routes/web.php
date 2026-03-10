<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AiController;

// ── Halaman utama → redirect ke login ─────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Autentikasi (tidak butuh login) ───────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Halaman kelompok (butuh login) ────────────────────────
Route::middleware('auth.custom')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Jadwal & Booking
    Route::get('/jadwal', [BookingController::class, 'index'])->name('schedule.index');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::delete('/booking/{id}', [BookingController::class, 'destroy'])->name('booking.destroy');
    
});

// ── Halaman Admin (butuh login + role admin) ──────────────
Route::middleware(['auth.custom', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Manajemen booking
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::delete('/booking/{id}', [AdminController::class, 'destroyBooking'])->name('booking.destroy');

    // Manajemen user
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');

    // Atur limit
    Route::post('/users/{id}/limit', [AdminController::class, 'setLimit'])->name('users.limit');

    // Reset manual
    Route::post('/reset-limits', [AdminController::class, 'resetAllLimits'])->name('reset.limits');
});
Route::get('/profile/ganti-password', [ProfileController::class, 'showGantiPassword'])->name('profile.ganti-password');
Route::post('/profile/ganti-password', [ProfileController::class, 'gantiPassword'])->name('profile.ganti-password.post');
Route::post('/admin/users/{id}/limit-harian', [AdminController::class, 'setLimitHarian'])->name('admin.users.limit.harian');
Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');
