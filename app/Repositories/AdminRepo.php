<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;

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

    public function getAllAdmins(): Collection
    {
        return $this->admin->newModelQuery()->with('roles')->get();
    }

    public function getActiveAdmins(): Collection
    {
        return $this->admin->newModelQuery()->where('status', 1)->with('roles')->get();
    }

    public function createAdmin(array $data): Admin
    {
        return $this->admin->create($data);
    }

    public function updateAdmin(int $id, array $data): bool
    {
        $updatedCount = $this->admin->newModelQuery()->where('id', $id)->update($data);
        return $updatedCount > 0;
    }

    public function deleteAdmin(int $id): bool
    {
        $deleted = $this->admin->newModelQuery()->where('id', $id)->delete();
        return $deleted > 0;
    }
}
