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
use App\Exceptions\CustomException;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    /**
     * 獲取角色列表
     */
    public function index(RoleListRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);

        $result = $this->roleService->getRoles($filters, $page, $limit);

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $result,
        ]);
    }

    /**
     * 創建角色
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = $this->roleService->create($data);

        return response()->json([
            'status' => 0,
            'message' => '角色創建成功',
            'data' => $role,
        ]);
    }

    /**
     * 獲取單個角色
     */
    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->findById($id);
        if (!$role) {
            throw new CustomException(CustomException::ROLE_NOT_FOUND);
        }

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $role,
        ]);
    }

    /**
     * 更新角色
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $result = $this->roleService->update($id, $data);

        if (!$result) {
            throw new CustomException(CustomException::COMMON_FAILED);
        }

        $role = $this->roleService->findById($id);

        return response()->json([
            'status' => 0,
            'message' => '角色更新成功',
            'data' => $role,
        ]);
    }

    /**
     * 刪除角色
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->roleService->delete($id);
        if (!$result) {
            throw new CustomException(CustomException::COMMON_FAILED);
        }

        return response()->json([
            'status' => 0,
            'message' => '角色刪除成功',
            'data' => null,
        ]);
    }

    /**
     * 分配權限給角色
     */
    public function assignPermissions(AssignPermissionsRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $this->roleService->assignPermissions($id, $data['permissions']);

        return response()->json([
            'status' => 0,
            'message' => '權限分配成功',
            'data' => null,
        ]);
    }
}
