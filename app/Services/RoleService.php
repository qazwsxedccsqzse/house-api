<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepo;
use App\Repositories\PermissionRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function __construct(
        private RoleRepo $roleRepo,
        private PermissionRepo $permissionRepo
    ) {
    }

    /**
     * 獲取角色列表
     */
    public function getRoles(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $roles = $this->roleRepo->getRoles($filters, $page, $limit);

        return [
            'list' => $roles->items(),
            'total' => $roles->total(),
            'page' => $roles->currentPage(),
            'limit' => $roles->perPage(),
        ];
    }

    /**
     * 獲取所有角色
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepo->getAllRoles();
    }

    /**
     * 根據 ID 獲取角色
     */
    public function findById(int $id): ?Role
    {
        return $this->roleRepo->findById($id);
    }

    /**
     * 創建角色
     */
    public function create(array $data): Role
    {
        // 檢查角色代碼是否已存在
        if ($this->roleRepo->codeExists($data['code'])) {
            throw new \Exception('角色代碼已存在');
        }

        // 創建角色
        $role = $this->roleRepo->create($data);

        // 如果有權限資料，分配權限
        if (!empty($data['permissions'])) {
            $this->assignPermissions($role->id, $data['permissions']);
        }

        return $role;
    }

    /**
     * 更新角色
     */
    public function update(int $id, array $data): bool
    {
        $role = $this->roleRepo->findById($id);

        if (!$role) {
            throw new \Exception('角色不存在');
        }

        // 檢查角色代碼是否已存在（排除自己）
        if (isset($data['code']) && $this->roleRepo->codeExists($data['code'], $id)) {
            throw new \Exception('角色代碼已存在');
        }

        // 更新角色
        $result = $this->roleRepo->update($role, $data);

        // 如果有權限資料，重新分配權限
        if (isset($data['permissions'])) {
            $this->assignPermissions($id, $data['permissions']);
        }

        return $result;
    }

    /**
     * 刪除角色
     */
    public function delete(int $id): bool
    {
        $role = $this->roleRepo->findById($id);

        if (!$role) {
            throw new \Exception('角色不存在');
        }

        return $this->roleRepo->delete($role);
    }

    /**
     * 分配權限給角色
     */
    public function assignPermissions(int $roleId, array $permissionIds): void
    {
        // 檢查角色是否存在
        if (!$this->roleRepo->exists($roleId)) {
            throw new \Exception('角色不存在');
        }

        // 驗證權限是否存在
        foreach ($permissionIds as $permissionId) {
            if (!$this->permissionRepo->exists($permissionId)) {
                throw new \Exception("權限 ID {$permissionId} 不存在");
            }
        }

        $this->roleRepo->assignPermissions($roleId, $permissionIds);
    }

    /**
     * 獲取角色的權限列表
     */
    public function getRolePermissions(int $roleId): Collection
    {
        if (!$this->roleRepo->exists($roleId)) {
            throw new \Exception('角色不存在');
        }

        return $this->permissionRepo->getPermissionsByRoleId($roleId);
    }

    /**
     * 獲取角色的權限 ID 列表
     */
    public function getRolePermissionIds(int $roleId): array
    {
        if (!$this->roleRepo->exists($roleId)) {
            throw new \Exception('角色不存在');
        }

        return $this->roleRepo->getRolePermissionIds($roleId);
    }
}
