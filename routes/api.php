<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;



Route::group(['prefix' => 'auth'], function () {
    // 登入
    Route::post('/signin', [AuthController::class, 'signin']);
    // 登出
    Route::post('/signout', [AuthController::class, 'logout'])->middleware('auth');
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
