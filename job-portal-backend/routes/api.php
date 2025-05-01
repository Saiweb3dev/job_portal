<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\AuthController;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);

    // Role-specific routes
    Route::middleware('role:admin')->group(function () {
        // Admin-only routes
    });

    Route::middleware('role:employer')->group(function () {
        // Employer-only routes
        Route::post('/jobs', [JobController::class, 'store']);
        Route::put('/jobs/{id}', [JobController::class, 'update']);
        Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    });

    Route::middleware('role:applicant')->group(function () {
        // Applicant-only routes
    });
});

// Public read endpoints
Route::prefix('v1')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{id}', [JobController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}/jobs', [CategoryController::class, 'jobs']);
});
