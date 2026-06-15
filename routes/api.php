<?php

use App\Http\Controllers\JobController;
use App\Http\Middleware\AttachJwtFromCookie;
use Illuminate\Support\Facades\Route;

Route::post('jobs', [JobController::class, 'store']);

Route::get('/tmp', function () {
  return [
    'sys_get_temp_dir' => sys_get_temp_dir(),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
  ];
});

Route::get('/phpini', function () {
  return php_ini_loaded_file();
});

Route::middleware([AttachJwtFromCookie::class, 'auth:api'])->group(function () {
  //Route::get('jobs', [JobController::class, 'index']);
});