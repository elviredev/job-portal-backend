<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\JobController;
use App\Http\Middleware\AttachJwtFromCookie;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google-login', [GoogleLoginController::class, 'googleLogin']);

Route::middleware([AttachJwtFromCookie::class, 'auth:api'])->group(function () {
  //Route::get('jobs', [JobController::class, 'index']);

  // recruiter only
  Route::middleware('role:recruiter')->group(function () {
    // all routes for recruiter
  });

  // user only
  Route::middleware('role:user')->group(function () {
    // all routes for user
  });
});

Route::post('jobs', [JobController::class, 'store']);