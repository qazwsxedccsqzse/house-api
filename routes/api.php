<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MemberController;

// 管理者登入註冊相關
Route::group(['prefix' => 'auth'], function () {
    // 登入
    Route::post('/signin', [AuthController::class, 'signin']);
    // 登出
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('admin.token');
});

// 管理者列表相關 api
Route::group(['prefix' => 'admins', 'middleware' => 'admin.token'], function () {
    // 管理者列表
    Route::get('/', [AdminController::class, 'index']);
    // 新增管理者
    Route::post('/', [AdminController::class, 'store']);
    // 更新管理者
    Route::put('/{id}', [AdminController::class, 'update']);
    // 刪除管理者
    Route::delete('/{id}', [AdminController::class, 'destroy']);
    // 分配角色
    Route::put('/{id}/roles', [AdminController::class, 'assignRoles']);
});

// 用戶列表相關 api
Route::group(['prefix' => 'members', 'middleware' => 'admin.token'], function () {
    // 用戶列表
    Route::get('/', [MemberController::class, 'index']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
