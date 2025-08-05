<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

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

// 通知相關 api
Route::group(['prefix' => 'notifications', 'middleware' => 'admin.token'], function () {
    // 取得通知列表
    Route::get('/', [NotificationController::class, 'index']);
    // 標記通知為已讀
    Route::put('/read/{id}', [NotificationController::class, 'markAsRead']);
    // 標記所有通知為已讀
    Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
});

// 公告相關 api
Route::group(['prefix' => 'notices', 'middleware' => 'admin.token'], function () {
    // 取得公告列表
    Route::get('/', [NoticeController::class, 'index']);
    // 創建公告
    Route::post('/', [NoticeController::class, 'store']);
    // 取得單個公告
    Route::get('/{id}', [NoticeController::class, 'show']);
    // 更新公告
    Route::put('/{id}', [NoticeController::class, 'update']);
    // 刪除公告
    Route::delete('/{id}', [NoticeController::class, 'destroy']);
});

// 角色相關 api
Route::group(['prefix' => 'roles', 'middleware' => 'admin.token'], function () {
    // 取得角色列表
    Route::get('/', [RoleController::class, 'index']);
    // 創建角色
    Route::post('/', [RoleController::class, 'store']);
    // 取得單個角色
    Route::get('/{id}', [RoleController::class, 'show']);
    // 更新角色
    Route::put('/{id}', [RoleController::class, 'update']);
    // 刪除角色
    Route::delete('/{id}', [RoleController::class, 'destroy']);
    // 分配角色權限
    Route::put('/{id}/permissions', [RoleController::class, 'assignPermissions']);
});

// 權限相關 api
Route::group(['prefix' => 'permissions', 'middleware' => 'admin.token'], function () {
    // 取得權限列表
    Route::get('/', [PermissionController::class, 'index']);
    // 創建權限
    Route::post('/', [PermissionController::class, 'store']);
    // 取得單個權限
    Route::get('/{id}', [PermissionController::class, 'show']);
    // 更新權限
    Route::put('/{id}', [PermissionController::class, 'update']);
    // 刪除權限
    Route::delete('/{id}', [PermissionController::class, 'destroy']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
