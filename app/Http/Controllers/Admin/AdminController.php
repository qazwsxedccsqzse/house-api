<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Http\Requests\Admin\AdminListRequest;

class AdminController extends Controller
{
    public function __construct(private AdminService $adminService)
    {
    }

    public function index(AdminListRequest $request)
    {
        $data = $request->validated();

        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;
        $search = $data['search'] ?? null;

        $admins = $this->adminService->getAllAdmins($page, $limit, $search);

        // 格式化响应数据
        $formattedList = $admins->getCollection()->map(function ($admin) {
            return [
                'id' => (string) $admin->id,
                'username' => $admin->username,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
                'roles' => $admin->roles->pluck('code')->toArray(),
                'createdAt' => $admin->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => [
                'list' => $formattedList,
                'total' => $admins->total(),
                'page' => $admins->currentPage(),
                'limit' => $admins->perPage(),
            ],
        ]);
    }
}
