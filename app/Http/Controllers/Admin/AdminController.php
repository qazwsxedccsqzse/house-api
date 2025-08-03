<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\AssignRolesRequest;

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

    public function store(CreateAdminRequest $request)
    {
        $data = $request->validated();
        $admin = $this->adminService->createAdmin($data);

        return response()->json([
            'status' => 0,
            'message' => '管理員創建成功',
            'data' => [
                'id' => (string) $admin->id,
                'username' => $admin->username,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
                'roles' => $admin->roles->pluck('code')->toArray(),
                'createdAt' => $admin->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function update(UpdateAdminRequest $request, int $id)
    {
        $data = $request->validated();
        $success = $this->adminService->updateAdmin($id, $data);

        if (!$success) {
            return response()->json([
                'status' => -1,
                'message' => '管理員不存在',
                'data' => null,
            ], 404);
        }

        // 獲取更新後的管理員信息
        $admin = $this->adminService->getAdminById($id);
        if (!$admin) {
            return response()->json([
                'status' => -1,
                'message' => '管理員不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '管理員更新成功',
            'data' => [
                'id' => (string) $admin->id,
                'username' => $admin->username,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
                'roles' => $admin->roles->pluck('code')->toArray(),
                'updatedAt' => $admin->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy(int $id)
    {
        $success = $this->adminService->deleteAdmin($id);

        if (!$success) {
            return response()->json([
                'status' => -1,
                'message' => '管理員不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '管理員刪除成功',
            'data' => null,
        ]);
    }

    public function assignRoles(AssignRolesRequest $request, int $id)
    {
        $data = $request->validated();
        $success = $this->adminService->assignRoles($id, $data['roles']);

        if (!$success) {
            return response()->json([
                'status' => -1,
                'message' => '管理員不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '角色分配成功',
            'data' => null,
        ]);
    }
}
