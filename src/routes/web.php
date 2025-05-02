<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('app')
    ->middleware(['auth', 'verified'])
    ->name('app.')
    ->group(function () {
        Route::get('/time-tracking', [TimeEntryController::class, 'index'])->name('time-tracking.index');
        Route::post('/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
        Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update'])->name('time-entries.update');
        Route::put('/time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stop'])->name('time-entries.stop');
        Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');
    });

require __DIR__ . '/auth.php';
