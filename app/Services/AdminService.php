<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepo;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\CustomException;
use App\Foundations\RedisHelper;
use Illuminate\Support\Str;

class AdminService
{
    public function __construct (
        private AdminRepo $adminRepo,
        private RedisHelper $redisHelper
    ) {
    }

    public function signin(array $credentials): array
    {
        // 只支援 username 登入
        $username = $credentials['username'];

        $admin = $this->adminRepo->getAdminByUsername($username);
        if (!$admin) {
            throw new CustomException(CustomException::ADMIN_NOT_FOUND);
        }
        if (!Hash::check($credentials['password'], $admin->password)) {
            throw new CustomException(CustomException::ADMIN_PASSWORD_ERROR);
        }

        // 確認 access token 是否存在，存在的話重新生成
        $accessToken = '';
        do {
            $accessToken = Str::random(60);
        } while ($this->redisHelper->exists('admin:token:' . $accessToken));


        $this->redisHelper->pipeline(function ($pipe) use ($admin, $accessToken) {
            $adminJson = json_encode($admin);
            $pipe->set('admin:id:' . $admin->id, $adminJson);
            $pipe->set('admin:token:' . $accessToken, $admin->id);

            $pipe->expire('admin:id:' . $admin->id, 3600 * 24 * 30);
            $pipe->expire('admin:token:' . $accessToken, 3600 * 24 * 30);
        });

        // 載入角色和權限
        $admin->load(['roles.permissions']);

        // 建構使用者資料
        $adminInfo = [
            'id' => (string) $admin->id,
            'username' => $admin->username, // 使用 username 欄位
            'email' => $admin->email,
            'avatar' => '', // 暫時為空，後續可以新增頭像欄位
            'roles' => $admin->roles->map(function ($role) {
                return [
                    'id' => (string) $role->id,
                    'name' => $role->name,
                    'code' => $role->code,
                ];
            })->toArray(),
            'permissions' => $admin->roles->flatMap(function ($role) {
                return $role->permissions->map(function ($permission) {
                    return [
                        'id' => (string) $permission->id,
                        'name' => $permission->name,
                        'code' => $permission->code,
                    ];
                });
            })->unique('id')->values()->toArray(),
            'menu' => [], // 暫時為空陣列，後續可以實作選單邏輯
        ];

        return [
            'user' => $adminInfo,
            'accessToken' => $accessToken,
            'refreshToken' => $accessToken,
        ];
    }

    public function createAdmin(array $data): Admin
    {
        return $this->adminRepo->createAdmin($data);
    }

    public function updateAdmin(int $id, array $data): bool
    {
        return $this->adminRepo->updateAdmin($id, $data);
    }

    public function deleteAdmin(int $id): bool
    {
        return $this->adminRepo->deleteAdmin($id);
    }

    public function getAllAdmins(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->adminRepo->getAllAdmins();
    }

    public function getActiveAdmins(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->adminRepo->getActiveAdmins();
    }

    /**
     * 登出
     */
    public function logout(int $adminId, string $token): void
    {
        // 從 Redis 中刪除相關的 token 和 admin 記錄
        $this->redisHelper->pipeline(function ($pipe) use ($adminId, $token) {
            $pipe->del('admin:token:' . $token);
            $pipe->del('admin:id:' . $adminId);
        });
    }
}
