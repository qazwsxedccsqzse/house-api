<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionListRequest;
use App\Http\Requests\Admin\CreatePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use App\Exceptions\CustomException;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {}

    /**
     * 獲取權限列表
     */
    public function index(PermissionListRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);

        $result = $this->permissionService->getPermissions($filters, $page, $limit);

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $result,
        ]);
    }

    /**
     * 創建權限
     */
    public function store(CreatePermissionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $permission = $this->permissionService->create($data);

        return response()->json([
            'status' => 0,
            'message' => '權限創建成功',
            'data' => $permission,
        ], 201);
    }

    /**
     * 獲取單個權限
     */
    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->findById($id);
        if (!$permission) {
            throw new CustomException(CustomException::PERMISSION_NOT_FOUND);
        }

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $permission,
        ]);
    }

    /**
     * 更新權限
     */
    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $result = $this->permissionService->update($id, $data);

        if (!$result) {
            throw new CustomException(CustomException::COMMON_FAILED);
        }

        $permission = $this->permissionService->findById($id);

        return response()->json([
            'status' => 0,
            'message' => '權限更新成功',
            'data' => $permission,
        ]);
    }

    /**
     * 刪除權限
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->permissionService->delete($id);

        if (!$result) {
            throw new CustomException(CustomException::COMMON_FAILED);
        }

        return response()->json([
            'status' => 0,
            'message' => '權限刪除成功',
            'data' => null,
        ]);
    }
}
