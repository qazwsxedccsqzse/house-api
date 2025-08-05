<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionListRequest;
use App\Http\Requests\Admin\CreatePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {
    }

    /**
     * 獲取權限列表
     */
    public function index(PermissionListRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $page = (int) ($filters['page'] ?? 1);
            $limit = (int) ($filters['limit'] ?? 10);

            $result = $this->permissionService->getPermissions($filters, $page, $limit);

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
     * 創建權限
     */
    public function store(CreatePermissionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $permission = $this->permissionService->create($data);

            return response()->json([
                'status' => 0,
                'message' => '權限創建成功',
                'data' => $permission,
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
     * 獲取單個權限
     */
    public function show(int $id): JsonResponse
    {
        try {
            $permission = $this->permissionService->findById($id);

            if (!$permission) {
                return response()->json([
                    'status' => -1,
                    'message' => '權限不存在',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 0,
                'message' => '',
                'data' => $permission,
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
     * 更新權限
     */
    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->permissionService->update($id, $data);

            if (!$result) {
                return response()->json([
                    'status' => -1,
                    'message' => '權限更新失敗',
                    'data' => null,
                ], 500);
            }

            $permission = $this->permissionService->findById($id);

            return response()->json([
                'status' => 0,
                'message' => '權限更新成功',
                'data' => $permission,
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
     * 刪除權限
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->permissionService->delete($id);

            if (!$result) {
                return response()->json([
                    'status' => -1,
                    'message' => '權限刪除失敗',
                    'data' => null,
                ], 500);
            }

            return response()->json([
                'status' => 0,
                'message' => '權限刪除成功',
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
