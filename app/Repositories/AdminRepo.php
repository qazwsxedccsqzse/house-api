<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
