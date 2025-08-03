<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;


// 管理者登入註冊相關
Route::group(['prefix' => 'auth'], function () {
    // 登入
    Route::post('/signin', [AuthController::class, 'signin']);
    // 登出
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('admin.token');
});

// 管理者列表相關 api
Route::group(['prefix' => 'admins', 'middleware' => 'admin.token'], function () {
    Route::get('/', [AdminController::class, 'index']);
});



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
