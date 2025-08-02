<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_creates_correct_permissions(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->assertDatabaseCount('permissions', 11);

        $this->assertDatabaseHas('permissions', [
            'code' => 'notice:read',
            'name' => '公告管理讀取',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('permissions', [
            'code' => 'admin:edit',
            'name' => '管理員列表編輯',
            'status' => 1,
        ]);
    }

    public function test_role_seeder_creates_correct_roles(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->assertDatabaseCount('roles', 3);

        $this->assertDatabaseHas('roles', [
            'code' => 'super-admin',
            'name' => '超級管理員',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('roles', [
            'code' => 'content-admin',
            'name' => '內容管理員',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('roles', [
            'code' => 'customer-service',
            'name' => '客服人員',
            'status' => 1,
        ]);
    }

    public function test_role_permission_seeder_assigns_correct_permissions(): void
    {
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        // 檢查超級管理員擁有所有權限
        $superAdminRole = Role::where('code', 'super-admin')->first();
        $this->assertEquals(11, $superAdminRole->permissions->count());

        // 檢查內容管理員只有公告相關權限
        $contentAdminRole = Role::where('code', 'content-admin')->first();
        $this->assertEquals(2, $contentAdminRole->permissions->count());
        $this->assertTrue($contentAdminRole->permissions->contains('code', 'notice:read'));
        $this->assertTrue($contentAdminRole->permissions->contains('code', 'notice:edit'));

        // 檢查客服人員只有會員相關權限
        $customerServiceRole = Role::where('code', 'customer-service')->first();
        $this->assertEquals(2, $customerServiceRole->permissions->count());
        $this->assertTrue($customerServiceRole->permissions->contains('code', 'member:read'));
        $this->assertTrue($customerServiceRole->permissions->contains('code', 'member:edit'));
    }

    public function test_admin_can_have_roles_and_permissions(): void
    {
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $admin = Admin::factory()->create();
        $superAdminRole = Role::where('code', 'super-admin')->first();
        $permission = Permission::where('code', 'notice:read')->first();

        // 分配角色給管理員
        $admin->roles()->attach($superAdminRole->id);

        $this->assertTrue($admin->roles->contains($superAdminRole));
        $this->assertTrue($superAdminRole->permissions->contains($permission));
    }

    public function test_admin_has_permission_methods(): void
    {
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $admin = Admin::factory()->create();
        $superAdminRole = Role::where('code', 'super-admin')->first();

        // 分配角色給管理員
        $admin->roles()->attach($superAdminRole->id);

        $this->assertTrue($admin->hasPermission('notice:read'));
        $this->assertTrue($admin->hasPermission('admin:edit'));
        $this->assertFalse($admin->hasPermission('non-existent:permission'));
    }

    public function test_content_admin_has_limited_permissions(): void
    {
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $admin = Admin::factory()->create();
        $contentAdminRole = Role::where('code', 'content-admin')->first();

        // 分配內容管理員角色
        $admin->roles()->attach($contentAdminRole->id);

        // 應該有公告相關權限
        $this->assertTrue($admin->hasPermission('notice:read'));
        $this->assertTrue($admin->hasPermission('notice:edit'));

        // 不應該有其他權限
        $this->assertFalse($admin->hasPermission('member:read'));
        $this->assertFalse($admin->hasPermission('admin:edit'));
    }

    public function test_customer_service_has_member_permissions(): void
    {
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $admin = Admin::factory()->create();
        $customerServiceRole = Role::where('code', 'customer-service')->first();

        // 分配客服人員角色
        $admin->roles()->attach($customerServiceRole->id);

        // 應該有會員相關權限
        $this->assertTrue($admin->hasPermission('member:read'));
        $this->assertTrue($admin->hasPermission('member:edit'));

        // 不應該有其他權限
        $this->assertFalse($admin->hasPermission('notice:read'));
        $this->assertFalse($admin->hasPermission('admin:edit'));
    }
}
