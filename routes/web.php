<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



// Public landing page
Route::get('/', [EventController::class, 'index'])->name('home');

// Public event detail
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');




// ── Public ──
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// ── Guest only (redirect if already logged in) ──
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

// ── Authenticated ──
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (all roles)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});



// ── Admin only ──
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',            [AdminController::class, 'index'])->name('dashboard');
    Route::patch('/users/{user}/toggle',[AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::patch('/users/{user}/role',  [AdminController::class, 'changeRole'])->name('users.role');
    Route::delete('/events/{event}',    [AdminController::class, 'deleteEvent'])->name('events.delete');
});
