<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 超級管理員 - 擁有所有權限
        $superAdminRole = Role::where('code', 'super-admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->permissions()->sync($allPermissions->pluck('id')->toArray());
        }

        // 內容管理員 - 只有公告相關權限
        $contentAdminRole = Role::where('code', 'content-admin')->first();
        if ($contentAdminRole) {
            $noticePermissions = Permission::whereIn('code', [
                'notice:read',
                'notice:edit'
            ])->get();
            $contentAdminRole->permissions()->sync($noticePermissions->pluck('id')->toArray());
        }

        // 客服人員 - 只有會員相關權限
        $customerServiceRole = Role::where('code', 'customer-service')->first();
        if ($customerServiceRole) {
            $memberPermissions = Permission::whereIn('code', [
                'member:read',
                'member:edit'
            ])->get();
            $customerServiceRole->permissions()->sync($memberPermissions->pluck('id')->toArray());
        }
    }
}
