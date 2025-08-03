<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\AdminSignInRequest;
use App\Services\AdminService;

class AuthController extends Controller
{
    public function __construct(private AdminService $adminService)
    {
    }

    /**
     * 登入
     */
    public function signin(AdminSignInRequest $request)
    {
        $credentials = $request->validated();
        $result = $this->adminService->signin($credentials);

        return response()->json([
            'status' => 0,
            'message' => '登入成功',
            'data' => $result,
        ]);
    }

    /**
     * 登出
     */
    public function logout(Request $request)
    {
        // 從 middleware 中獲取 admin ID
        $adminId = $request->input('admin_id');

        // 從 Authorization header 中獲取 token
        $authorization = $request->header('Authorization');
        $token = substr($authorization, 7); // 移除 "Bearer " 前綴

        // 從 Redis 中刪除 token
        $this->adminService->logout($adminId, $token);

        return response()->json([
            'status' => 0,
            'message' => '登出成功',
            'data' => null,
        ]);
    }
}
