<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleListRequest;
use App\Http\Requests\Admin\CreateRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Requests\Admin\AssignPermissionsRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {
    }

    /**
     * 獲取角色列表
     */
    public function index(RoleListRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $page = (int) ($filters['page'] ?? 1);
            $limit = (int) ($filters['limit'] ?? 10);

            $result = $this->roleService->getRoles($filters, $page, $limit);

            return response()->json([
                'status' => 0,
                'message' => '',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 創建角色
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $role = $this->roleService->create($data);

            return response()->json([
                'status' => 0,
                'message' => '角色創建成功',
                'data' => $role,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 獲取單個角色
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->findById($id);

            if (!$role) {
                return response()->json([
                    'status' => -1,
                    'message' => '角色不存在',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 0,
                'message' => '',
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 更新角色
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->roleService->update($id, $data);

            if (!$result) {
                return response()->json([
                    'status' => -1,
                    'message' => '角色更新失敗',
                    'data' => null,
                ], 500);
            }

            $role = $this->roleService->findById($id);

            return response()->json([
                'status' => 0,
                'message' => '角色更新成功',
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 刪除角色
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->roleService->delete($id);

            if (!$result) {
                return response()->json([
                    'status' => -1,
                    'message' => '角色刪除失敗',
                    'data' => null,
                ], 500);
            }

            return response()->json([
                'status' => 0,
                'message' => '角色刪除成功',
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * 分配權限給角色
     */
    public function assignPermissions(AssignPermissionsRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->roleService->assignPermissions($id, $data['permissions']);

            return response()->json([
                'status' => 0,
                'message' => '權限分配成功',
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
