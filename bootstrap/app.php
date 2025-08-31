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
    // 用戶端 API 路由 - 使用 /api/v1/frontend 前綴
    ->withRouting(
        api: __DIR__.'/../routes/api_frontend.php',
        apiPrefix: 'api/v1/frontend',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 添加 CORS middleware 到全域最前面
        $middleware->prepend(\App\Http\Middlewares\CorsMiddleware::class);

        $middleware->alias([
            'admin.token' => \App\Http\Middlewares\AdminTokenMiddleware::class,
            'member.token' => \App\Http\Middlewares\MemberTokenMiddleware::class,
            'member.optional-token' => \App\Http\Middlewares\MemberOptionalTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
