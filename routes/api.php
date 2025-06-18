<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\ClientController;

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
    Route::put('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    // Reminder routes
    Route::get('reminders', [ReminderController::class, 'index']);
    Route::get('reminder-settings', [ReminderController::class, 'settings']);
    Route::put('reminder-settings/{reminder}', [ReminderController::class, 'updateSetting']);
    Route::post('appointments/{appointment}/trigger-reminder', [ReminderController::class, 'trigger']);
    Route::post('reminders/{reminder}/retry', [ReminderController::class, 'retry']);

    // Client notification preferences
    Route::put('/clients/{client}/notification-preferences', [ClientController::class, 'updateNotificationPreferences'])
        ->name('clients.update-notification-preferences');

    // Client routes
    Route::apiResource('clients', ClientController::class);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/reminders', [App\Http\Controllers\Api\Admin\ReminderController::class, 'index']);
    Route::get('/reminders/stats', [App\Http\Controllers\Api\Admin\ReminderController::class, 'stats']);
    Route::get('/reminder-settings', [App\Http\Controllers\Api\Admin\ReminderController::class, 'settings']);
});
