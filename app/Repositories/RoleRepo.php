<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepo
{
    public function __construct(
        private Role $role,
        private RolePermission $rolePermission
    ) {
    }

    /**
     * 獲取角色列表（分頁）
     */
    public function getRoles(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->role->with('permissions');

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
     * 獲取所有角色
     */
    public function getAllRoles(): Collection
    {
        return $this->role->with('permissions')->get();
    }

    /**
     * 根據 ID 獲取角色
     */
    public function findById(int $id): ?Role
    {
        return $this->role->with('permissions')->find($id);
    }

    /**
     * 根據代碼獲取角色
     */
    public function findByCode(string $code): ?Role
    {
        return $this->role->where('code', $code)->first();
    }

    /**
     * 創建角色
     */
    public function create(array $data): Role
    {
        return $this->role->create($data);
    }

    /**
     * 更新角色
     */
    public function update(Role $role, array $data): bool
    {
        return $role->update($data);
    }

    /**
     * 刪除角色
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * 分配權限給角色
     */
    public function assignPermissions(int $roleId, array $permissionIds): void
    {
        // 先刪除現有的權限分配
        $this->rolePermission->where('role_id', $roleId)->delete();

        // 新增新的權限分配
        $rolePermissions = [];
        $now = now();
        foreach ($permissionIds as $permissionId) {
            $rolePermissions[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rolePermissions)) {
            $this->rolePermission->insert($rolePermissions);
        }
    }

    /**
     * 獲取角色的權限 ID 列表
     */
    public function getRolePermissionIds(int $roleId): array
    {
        return $this->rolePermission->where('role_id', $roleId)
                                   ->pluck('permission_id')
                                   ->toArray();
    }

    /**
     * 檢查角色是否存在
     */
    public function exists(int $id): bool
    {
        return $this->role->where('id', $id)->exists();
    }

    /**
     * 檢查角色代碼是否已存在
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $query = $this->role->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
