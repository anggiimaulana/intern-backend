<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/records/data', [App\Http\Controllers\RecordsController::class, 'index']);
    Route::post('/records/data', [App\Http\Controllers\RecordsController::class, 'store']);
    Route::put('/records/data/{id}', [App\Http\Controllers\RecordsController::class, 'update']);
    Route::delete('/records/data/{id}', [App\Http\Controllers\RecordsController::class, 'destroy']);
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
