<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:admin,account_manager,copywriter'])->group(function () {
    Route::resource('clients', ClientController::class)->except(['show']);
});

require __DIR__.'/settings.php';
