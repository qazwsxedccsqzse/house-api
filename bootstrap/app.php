<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // 管理者 API 路由 - 使用 /api/v1/admin 前綴
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1/admin',
    )
    // 用戶端 API 路由 - 使用 /api/v1/user 前綴
    ->withRouting(
        api: __DIR__.'/../routes/api_user.php',
        apiPrefix: 'api/v1/user',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.token' => \App\Http\Middlewares\AdminTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
