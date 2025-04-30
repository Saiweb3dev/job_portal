<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\JobController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Public read endpoints
Route::prefix('v1')->group(function () {
    Route::get('/jobs',[JobController::class, 'index']);
    Route::get('/jobs/{id}',[JobController::class,'show']);
    Route::get('/categories',[CategoryController::class,'index']);
    Route::get('/categories/{id}/jobs',[CategoryController::class,'jobs']);
});
