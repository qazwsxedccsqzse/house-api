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
        $adminId = $this->redisHelper->get('admin:token:' . $token);
        if (!$adminId) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 從 admin ID 獲取完整的 admin 資料
        $adminJson = $this->redisHelper->get('admin:id:' . $adminId);
        if (!$adminJson) {
            return response()->json([
                'status' => -1,
                'message' => '請登入',
                'data' => null,
            ], 403);
        }

        // 將 admin 資料和 admin ID 添加到請求中，供後續使用
        $request->merge([
            'admin' => json_decode($adminJson, true),
            'admin_id' => (int) $adminId
        ]);

        return $next($request);
    }
}
