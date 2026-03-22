<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\OrganizerController;
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




// ── User & Organizer Dashboard ──
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Registrations
    Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('register.event');
    Route::patch('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('register.cancel');
});




// ── Organizer only ──
Route::middleware(['auth', 'role:organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/events',                    [OrganizerController::class, 'index'])->name('events');
    Route::get('/events/create',             [OrganizerController::class, 'create'])->name('events.create');
    Route::post('/events',                   [OrganizerController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit',       [OrganizerController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}',            [OrganizerController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}',         [OrganizerController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/participants',[OrganizerController::class, 'participants'])->name('events.participants');
});

