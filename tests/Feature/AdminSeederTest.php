<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_sets_timestamps_correctly(): void
    {
        // 先執行 RoleSeeder 來創建角色
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // 執行 AdminSeeder
        $this->seed(AdminSeeder::class);

        // 檢查 admin 是否被創建
        $admin = Admin::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);

        // 檢查角色關聯是否正確設置
        $superAdminRole = Role::where('code', 'super-admin')->first();
        $this->assertNotNull($superAdminRole);

        // 檢查 admin_roles 表中的 timestamps 是否正確設置
        $adminRole = DB::table('admin_roles')
            ->where('admin_id', $admin->id)
            ->where('role_id', $superAdminRole->id)
            ->first();

        $this->assertNotNull($adminRole);
        $this->assertNotNull($adminRole->created_at);
        $this->assertNotNull($adminRole->updated_at);

        // 檢查 timestamps 不是 null 或空字串
        $this->assertNotEmpty($adminRole->created_at);
        $this->assertNotEmpty($adminRole->updated_at);
    }
}
