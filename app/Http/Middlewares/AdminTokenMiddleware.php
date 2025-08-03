<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use App\Foundations\RedisHelper;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenMiddleware
{
    public function __construct(private RedisHelper $redisHelper)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 檢查 Authorization header
        $authorization = $request->header('Authorization');
        if (!$authorization) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 檢查 Bearer token 格式
        if (!str_starts_with($authorization, 'Bearer ')) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 提取 token
        $token = substr($authorization, 7); // 移除 "Bearer " 前綴
        if (empty($token)) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 檢查 token 是否在 Redis 中存在
        $adminJson = $this->redisHelper->get('admin:token:' . $token);
        if (!$adminJson) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 將 admin 資料添加到請求中，供後續使用
        $request->merge(['admin' => json_decode($adminJson, true)]);

        return $next($request);
    }
}
