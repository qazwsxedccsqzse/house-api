<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'id' => 1,
                'name' => '公告管理讀取',
                'code' => 'notice:read',
                'description' => '查看公告列表和詳情',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'name' => '公告管理編輯',
                'code' => 'notice:edit',
                'description' => '創建、編輯、刪除公告',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 3,
                'name' => '會員管理讀取',
                'code' => 'member:read',
                'description' => '查看會員列表和詳情',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 4,
                'name' => '會員管理編輯',
                'code' => 'member:edit',
                'description' => '編輯會員資訊',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 5,
                'name' => '帳務管理讀取',
                'code' => 'order:read',
                'description' => '查看帳務列表和詳情',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 6,
                'name' => '權限列表讀取',
                'code' => 'permission:read',
                'description' => '查看權限列表',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 7,
                'name' => '權限列表編輯',
                'code' => 'permission:edit',
                'description' => '創建、編輯、刪除權限',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 8,
                'name' => '角色列表讀取',
                'code' => 'role:read',
                'description' => '查看角色列表',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 9,
                'name' => '角色列表編輯',
                'code' => 'role:edit',
                'description' => '創建、編輯、刪除角色',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 10,
                'name' => '管理員列表讀取',
                'code' => 'admin:read',
                'description' => '查看管理員列表',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 11,
                'name' => '管理員列表編輯',
                'code' => 'admin:edit',
                'description' => '創建、編輯、刪除管理員',
                'status' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                $permission
            );
        }
    }
}
