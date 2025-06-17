<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ReminderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Appointment routes
    Route::apiResource('appointments', AppointmentController::class);

    // Reminder routes
    Route::get('reminders', [ReminderController::class, 'index']);
    Route::post('appointments/{appointment}/trigger-reminder', [ReminderController::class, 'trigger']);
    Route::post('reminders/{reminder}/retry', [ReminderController::class, 'retry']);
});
