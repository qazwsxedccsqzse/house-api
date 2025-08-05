<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionRepo
{
    public function __construct(
        private Permission $permission,
        private RolePermission $rolePermission
    ) {
    }

    /**
     * 獲取權限列表（分頁）
     */
    public function getPermissions(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->permission;

        // 搜尋條件
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // 狀態篩選
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 獲取所有權限
     */
    public function getAllPermissions(): Collection
    {
        return $this->permission->get();
    }

    /**
     * 根據 ID 獲取權限
     */
    public function findById(int $id): ?Permission
    {
        return $this->permission->find($id);
    }

    /**
     * 根據代碼獲取權限
     */
    public function findByCode(string $code): ?Permission
    {
        return $this->permission->where('code', $code)->first();
    }

    /**
     * 創建權限
     */
    public function create(array $data): Permission
    {
        return $this->permission->create($data);
    }

    /**
     * 更新權限
     */
    public function update(Permission $permission, array $data): bool
    {
        return $permission->update($data);
    }

    /**
     * 刪除權限
     */
    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * 獲取角色的權限列表
     */
    public function getPermissionsByRoleId(int $roleId): Collection
    {
        $permissionIds = $this->rolePermission->where('role_id', $roleId)->pluck('permission_id');
        return $this->permission->whereIn('id', $permissionIds)->get();
    }

    /**
     * 檢查權限是否存在
     */
    public function exists(int $id): bool
    {
        return $this->permission->where('id', $id)->exists();
    }

    /**
     * 檢查權限代碼是否已存在
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $query = $this->permission->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
