<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum — Token Guard for Mobile)
|--------------------------------------------------------------------------
|
| These routes are consumed by the Flutter mobile app.
| Public routes (register, login, forgot/reset password) have no auth.
| Protected routes require a valid Sanctum token via auth:sanctum middleware.
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/records', [DataController::class, 'index']);
    Route::post('/records', [DataController::class, 'store']);
    Route::put('/records/{id}', [DataController::class, 'update']);
    Route::delete('/records/{id}', [DataController::class, 'destroy']);
});
