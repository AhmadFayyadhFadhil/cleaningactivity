<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\CleaningTaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Area management (controller handles authorization)
    Route::apiResource('areas', AreaController::class);

    // Checklist items (controller handles authorization)
    Route::apiResource('checklist-items', ChecklistItemController::class);

    // Schedule management (controller handles authorization)
    Route::apiResource('schedules', ScheduleController::class);

    // Cleaning tasks (staff)
    Route::prefix('my-tasks')->group(function () {
        Route::get('/', [CleaningTaskController::class, 'myTasks']);
        Route::get('/{id}', [CleaningTaskController::class, 'show']);
        Route::post('/{id}/complete', [CleaningTaskController::class, 'complete']);
    });

    // Verification (supervisor)
    Route::prefix('verifications')->group(function () {
        Route::get('/pending', [VerificationController::class, 'pending']);
        Route::post('/{id}/approve', [VerificationController::class, 'approve']);
        Route::post('/{id}/reject', [VerificationController::class, 'reject']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/area-status', [DashboardController::class, 'areaStatus']);
    });
});