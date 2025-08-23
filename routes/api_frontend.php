<?php

use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanController;

// 用戶認證相關
Route::group(['prefix' => 'auth'], function () {
    // 用戶註冊
    Route::post('/register', function () {
        return response()->json(['message' => '用戶註冊端點 - 待實作']);
    });

    // 用戶登入
    Route::post('/login', function () {
        return response()->json(['message' => '用戶登入端點 - 待實作']);
    });

    // line oauth
    Route::group(['prefix' => 'line'], function () {
        // generate code verifier
        Route::get('/code-verifier', [OAuthController::class, 'generateCodeVerifier']);
        // callback url
        Route::get('/oauth', [OAuthController::class, 'lineOauthCallback']);
    });

    // 用戶登出
    Route::post('/logout', function () {
        return response()->json(['message' => '用戶登出端點 - 待實作']);
    })->middleware('auth:sanctum');
});

// 用戶資訊相關
Route::group(['prefix' => 'user', 'middleware' => ['member.token']], function () {
    // 取得用戶資訊
    Route::get('/me', [UserController::class, 'getUserInfo']);
    // 取得用戶訂單
    Route::get('/orders', [UserController::class, 'getUserOrders']);
});

// plans
Route::get('/plans', [PlanController::class, 'getPlans']);


// 測試端點
Route::get('/test', function () {
    return response()->json([
        'message' => '用戶端 API 測試成功',
        'timestamp' => now(),
        'prefix' => 'api/v1/user'
    ]);
});

// CORS 測試端點
Route::get('/cors-test', function () {
    return response()->json([
        'message' => 'CORS 測試成功',
        'timestamp' => now()
    ]);
});
