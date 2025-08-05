<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use App\Repositories\PermissionRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function __construct(
        private PermissionRepo $permissionRepo
    ) {
    }

    /**
     * 獲取權限列表
     */
    public function getPermissions(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $permissions = $this->permissionRepo->getPermissions($filters, $page, $limit);

        return [
            'list' => $permissions->items(),
            'total' => $permissions->total(),
            'page' => $permissions->currentPage(),
            'limit' => $permissions->perPage(),
        ];
    }

    /**
     * 獲取所有權限
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissionRepo->getAllPermissions();
    }

    /**
     * 根據 ID 獲取權限
     */
    public function findById(int $id): ?Permission
    {
        return $this->permissionRepo->findById($id);
    }

    /**
     * 創建權限
     */
    public function create(array $data): Permission
    {
        // 檢查權限代碼是否已存在
        if ($this->permissionRepo->codeExists($data['code'])) {
            throw new \Exception('權限代碼已存在');
        }

        return $this->permissionRepo->create($data);
    }

    /**
     * 更新權限
     */
    public function update(int $id, array $data): bool
    {
        $permission = $this->permissionRepo->findById($id);

        if (!$permission) {
            throw new \Exception('權限不存在');
        }

        // 檢查權限代碼是否已存在（排除自己）
        if (isset($data['code']) && $this->permissionRepo->codeExists($data['code'], $id)) {
            throw new \Exception('權限代碼已存在');
        }

        return $this->permissionRepo->update($permission, $data);
    }

    /**
     * 刪除權限
     */
    public function delete(int $id): bool
    {
        $permission = $this->permissionRepo->findById($id);

        if (!$permission) {
            throw new \Exception('權限不存在');
        }

        return $this->permissionRepo->delete($permission);
    }

    /**
     * 檢查權限是否存在
     */
    public function exists(int $id): bool
    {
        return $this->permissionRepo->exists($id);
    }

    /**
     * 根據代碼獲取權限
     */
    public function findByCode(string $code): ?Permission
    {
        return $this->permissionRepo->findByCode($code);
    }
}
