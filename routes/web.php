<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\QuarterController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:admin,account_manager,copywriter'])->group(function () {
    Route::resource('clients', ClientController::class)->except(['show']);

    Route::get('clients/{client}/quarters', [QuarterController::class, 'index'])->name('quarters.index');
    Route::post('clients/{client}/quarters', [QuarterController::class, 'store'])->name('quarters.store');
    Route::get('quarters/{quarter}', [QuarterController::class, 'show'])->name('quarters.show');
    Route::patch('quarters/{quarter}/status', [QuarterController::class, 'updateStatus'])->name('quarters.status');
});

require __DIR__.'/settings.php';
