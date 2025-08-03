<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminRepo
{
    public function __construct(private Admin $admin)
    {
    }

    public function getAdminByEmail(string $email): ?Admin
    {
        return $this->admin->newModelQuery()->where('email', $email)->first();
    }

    public function getAdminByUsername(string $username): ?Admin
    {
        // 只透過 username 欄位查找管理員
        return $this->admin->newModelQuery()
            ->where('username', $username)
            ->first();
    }

    public function getAdminById(int $id): ?Admin
    {
        return $this->admin->newModelQuery()->find($id);
    }

    public function getAllAdmins(int $page = 1, int $pageSize = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->admin->newModelQuery()->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate(
            perPage: $pageSize,
            page: $page,
            columns: ['*'],
            pageName: 'page',
        );
    }

    public function getActiveAdmins(): Collection
    {
        return $this->admin->newModelQuery()->where('status', 1)->with('roles')->get();
    }

    public function createAdmin(array $data): Admin
    {
        // 如果有密碼，進行加密
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin = $this->admin->create($data);

        // 如果有角色，進行關聯
        if (isset($data['roles']) && is_array($data['roles'])) {
            $roleIds = Role::whereIn('code', $data['roles'])->pluck('id')->toArray();
            $admin->roles()->attach($roleIds);
        }

        return $admin->load('roles');
    }

    public function updateAdmin(int $id, array $data): bool
    {
        $admin = $this->admin->find($id);
        if (!$admin) {
            return false;
        }

        // 如果有密碼，進行加密
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        // 如果有角色，更新關聯
        if (isset($data['roles']) && is_array($data['roles'])) {
            $roleIds = Role::whereIn('code', $data['roles'])->pluck('id')->toArray();
            $admin->roles()->sync($roleIds);
        }

        return true;
    }

    public function deleteAdmin(int $id): bool
    {
        $admin = $this->admin->find($id);
        if (!$admin) {
            return false;
        }

        // 刪除角色關聯
        $admin->roles()->detach();

        // 刪除管理員
        return $admin->delete();
    }

    public function assignRoles(int $adminId, array $roles): bool
    {
        $admin = $this->admin->find($adminId);
        if (!$admin) {
            return false;
        }

        $roleIds = Role::whereIn('code', $roles)->pluck('id')->toArray();
        $admin->roles()->sync($roleIds);

        return true;
    }
}
