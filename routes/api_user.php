<?php

use App\Http\Controllers\Api\OAuthController;
use Illuminate\Support\Facades\Route;

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


// 測試端點
Route::get('/test', function () {
    return response()->json([
        'message' => '用戶端 API 測試成功',
        'timestamp' => now(),
        'prefix' => 'api/v1/user'
    ]);
});