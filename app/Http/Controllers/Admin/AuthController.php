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
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '登出成功']);
    }
}
