<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\JobController;
use App\Http\Middleware\AttachJwtFromCookie;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google-login', [GoogleLoginController::class, 'googleLogin']);

// public routes
Route::get('jobs', [JobController::class, 'index']);

// routes with jwt auth to verify user
Route::middleware([AttachJwtFromCookie::class, 'auth:api'])->group(function () {
  Route::post('/auth/logout', [AuthController::class, 'logout']);
  Route::get('/auth/me', [AuthController::class, 'me']);

  // recruiter only
  Route::middleware('role:recruiter')->group(function () {
    // all routes for recruiter
    Route::post('jobs', [JobController::class, 'store']);
    Route::get('my-jobs', [JobController::class, 'myJobs']);
    Route::delete('jobs/{job}', [JobController::class, 'destroy']);
    Route::get('jobs/{job}', [JobController::class, 'show']);
  });

  // user only
  Route::middleware('role:user')->group(function () {
    // all routes for user
  });
});

