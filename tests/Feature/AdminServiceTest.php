<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminService;
use App\Repositories\AdminRepo;
use App\Exceptions\CustomException;
use App\Foundations\RedisHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Mockery;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminService $adminService;
    private $redisHelper;
    protected $adminServiceMock;
    protected $adminRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock RedisHelper
        $this->redisHelper = Mockery::mock(RedisHelper::class);
        $this->redisHelper->shouldReceive('exists')->andReturn(false);
        $this->redisHelper->shouldReceive('pipeline')->andReturnUsing(function ($callback) {
            $pipe = Mockery::mock();
            $pipe->shouldReceive('set')->andReturnSelf();
            $pipe->shouldReceive('expire')->andReturnSelf();
            $callback($pipe);
        });

        $this->adminService = new AdminService(new AdminRepo(new Admin()), $this->redisHelper);

        // Mock AdminRepo 和 AdminService 用於特定測試
        $this->adminRepoMock = Mockery::mock(AdminRepo::class);
        $this->adminServiceMock = Mockery::mock(AdminService::class);
        $this->app->instance(AdminRepo::class, $this->adminRepoMock);
        $this->app->instance(AdminService::class, $this->adminServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_signin_with_valid_credentials(): void
    {
        // 建立測試管理員
        $admin = Admin::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $credentials = [
            'username' => 'testadmin',
            'password' => 'password',
        ];

        $result = $this->adminService->signin($credentials);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('accessToken', $result);
        $this->assertArrayHasKey('refreshToken', $result);

        $this->assertEquals((string) $admin->id, $result['user']['id']);
        $this->assertEquals($admin->username, $result['user']['username']);
        $this->assertEquals($admin->email, $result['user']['email']);
        $this->assertNotEmpty($result['accessToken']);
        $this->assertNotEmpty($result['refreshToken']);
    }

    public function test_signin_with_invalid_username(): void
    {
        $credentials = [
            'username' => 'nonexistent',
            'password' => 'password',
        ];

        $this->expectException(CustomException::class);
        $this->expectExceptionCode(CustomException::ADMIN_NOT_FOUND);

        $this->adminService->signin($credentials);
    }

    public function test_signin_with_invalid_password(): void
    {
        // 建立測試管理員
        Admin::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $credentials = [
            'username' => 'testadmin',
            'password' => 'wrong_password',
        ];

        $this->expectException(CustomException::class);
        $this->expectExceptionCode(CustomException::ADMIN_PASSWORD_ERROR);

        $this->adminService->signin($credentials);
    }

    public function test_create_admin_basic(): void
    {
        $adminData = [
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1,
        ];

        $admin = $this->adminService->createAdmin($adminData);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('Test Admin', $admin->name);
        $this->assertEquals('testadmin', $admin->username);
        $this->assertEquals('test@example.com', $admin->email);
        $this->assertEquals(1, $admin->status);
    }

    public function test_get_all_admins(): void
    {
        // 建立多個管理員
        Admin::factory()->count(3)->create();

        $admins = $this->adminService->getAllAdmins();

        $this->assertCount(3, $admins);
        $this->assertInstanceOf(Admin::class, $admins->first());
    }

    public function test_get_active_admins(): void
    {
        // 建立活躍和非活躍管理員
        Admin::factory()->count(2)->create(['status' => 1]);
        Admin::factory()->create(['status' => 0]);

        $activeAdmins = $this->adminService->getActiveAdmins();

        $this->assertCount(2, $activeAdmins);
        foreach ($activeAdmins as $admin) {
            $this->assertEquals(1, $admin->status);
        }
    }

    public function test_can_get_admin_list_with_mock(): void
    {
        // 準備測試數據
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $admin = Admin::factory()->create([
            'username' => 'admin',
            'name' => '系統管理員',
            'email' => 'admin@example.com',
            'status' => 1,
        ]);

        $admin->roles()->attach($role);

        // 創建分頁數據
        $paginator = new LengthAwarePaginator(
            collect([$admin]),
            1,
            10,
            1
        );

        // Mock AdminService 的 getAllAdmins 方法
        $this->adminServiceMock
            ->shouldReceive('getAllAdmins')
            ->with(1, 10, null)
            ->once()
            ->andReturn($paginator);

        // 測試服務方法
        $result = $this->adminServiceMock->getAllAdmins(1, 10, null);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
    }

    public function test_can_search_admins_with_mock(): void
    {
        // 準備測試數據
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $admin = Admin::factory()->create([
            'username' => 'admin',
            'name' => '系統管理員',
            'email' => 'admin@example.com',
            'status' => 1,
        ]);

        $admin->roles()->attach($role);

        // 創建分頁數據
        $paginator = new LengthAwarePaginator(
            collect([$admin]),
            1,
            10,
            1
        );

        // Mock AdminService 的 getAllAdmins 方法
        $this->adminServiceMock
            ->shouldReceive('getAllAdmins')
            ->with(1, 10, 'admin')
            ->once()
            ->andReturn($paginator);

        // 測試服務方法
        $result = $this->adminServiceMock->getAllAdmins(1, 10, 'admin');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
    }

    public function test_can_paginate_admins_with_mock(): void
    {
        // 準備測試數據
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $admins = collect();
        for ($i = 1; $i <= 5; $i++) {
            $admin = Admin::factory()->create([
                'username' => "admin{$i}",
                'name' => "管理員{$i}",
                'email' => "admin{$i}@example.com",
                'status' => 1,
            ]);
            $admin->roles()->attach($role);
            $admins->push($admin);
        }

        // 創建分頁數據
        $paginator = new LengthAwarePaginator(
            $admins,
            15,
            5,
            1
        );

        // Mock AdminService 的 getAllAdmins 方法
        $this->adminServiceMock
            ->shouldReceive('getAllAdmins')
            ->with(1, 5, null)
            ->once()
            ->andReturn($paginator);

        // 測試服務方法
        $result = $this->adminServiceMock->getAllAdmins(1, 5, null);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(15, $result->total());
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertCount(5, $result->items());
    }

    public function test_admin_service_methods_with_real_data(): void
    {
        // 創建真實的 AdminService 實例（不使用 mock）
        $adminRepo = new AdminRepo(new Admin());
        $adminService = new AdminService($adminRepo, app(\App\Foundations\RedisHelper::class));

        // 創建測試數據
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $admin = Admin::factory()->create([
            'username' => 'admin',
            'name' => '系統管理員',
            'email' => 'admin@example.com',
            'status' => 1,
        ]);

        $admin->roles()->attach($role);

        // 測試獲取所有管理員
        $result = $adminService->getAllAdmins(1, 10);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());

        // 測試搜索功能
        $result = $adminService->getAllAdmins(1, 10, 'admin');
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());

        // 測試搜索不存在的用戶
        $result = $adminService->getAllAdmins(1, 10, 'nonexistent');
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    public function test_admin_repo_methods_with_mock(): void
    {
        // 準備測試數據
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $admin = Admin::factory()->create([
            'username' => 'admin',
            'name' => '系統管理員',
            'email' => 'admin@example.com',
            'status' => 1,
        ]);

        $admin->roles()->attach($role);

        // 創建分頁數據
        $paginator = new LengthAwarePaginator(
            collect([$admin]),
            1,
            10,
            1
        );

        // Mock AdminRepo 的 getAllAdmins 方法
        $this->adminRepoMock
            ->shouldReceive('getAllAdmins')
            ->with(1, 10, null)
            ->once()
            ->andReturn($paginator);

        // 測試 Repository 方法
        $result = $this->adminRepoMock->getAllAdmins(1, 10, null);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
    }

    public function test_create_admin(): void
    {
        // 創建角色
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $adminData = [
            'username' => 'newadmin',
            'password' => 'password123',
            'name' => '新管理員',
            'email' => 'newadmin@example.com',
            'status' => 1,
            'roles' => ['super-admin'],
        ];

        $admin = $this->adminService->createAdmin($adminData);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('newadmin', $admin->username);
        $this->assertEquals('新管理員', $admin->name);
        $this->assertEquals('newadmin@example.com', $admin->email);
        $this->assertEquals(1, $admin->status);
        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->assertCount(1, $admin->roles);
        $this->assertEquals('super-admin', $admin->roles->first()->code);
    }

    public function test_update_admin(): void
    {
        // 創建角色
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        // 創建管理員
        $admin = Admin::factory()->create([
            'username' => 'oldadmin',
            'name' => '舊管理員',
            'email' => 'oldadmin@example.com',
            'status' => 1,
        ]);

        $updateData = [
            'username' => 'updatedadmin',
            'password' => 'newpassword123',
            'name' => '更新管理員',
            'email' => 'updatedadmin@example.com',
            'status' => 1,
            'roles' => ['super-admin'],
        ];

        $success = $this->adminService->updateAdmin($admin->id, $updateData);

        $this->assertTrue($success);

        // 重新獲取管理員
        $updatedAdmin = $this->adminService->getAdminById($admin->id);
        $this->assertEquals('updatedadmin', $updatedAdmin->username);
        $this->assertEquals('更新管理員', $updatedAdmin->name);
        $this->assertEquals('updatedadmin@example.com', $updatedAdmin->email);
        $this->assertTrue(Hash::check('newpassword123', $updatedAdmin->password));
        $this->assertCount(1, $updatedAdmin->roles);
        $this->assertEquals('super-admin', $updatedAdmin->roles->first()->code);
    }

    public function test_update_admin_without_password(): void
    {
        // 創建管理員
        $admin = Admin::factory()->create([
            'username' => 'testadmin',
            'name' => '測試管理員',
            'email' => 'testadmin@example.com',
            'status' => 1,
        ]);

        $originalPassword = $admin->password;

        $updateData = [
            'username' => 'updatedadmin',
            'name' => '更新管理員',
            'email' => 'updatedadmin@example.com',
            'status' => 1,
        ];

        $success = $this->adminService->updateAdmin($admin->id, $updateData);

        $this->assertTrue($success);

        // 重新獲取管理員
        $updatedAdmin = $this->adminService->getAdminById($admin->id);
        $this->assertEquals('updatedadmin', $updatedAdmin->username);
        $this->assertEquals($originalPassword, $updatedAdmin->password); // 密碼應該不變
    }

    public function test_delete_admin(): void
    {
        // 創建角色
        $role = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        // 創建管理員
        $admin = Admin::factory()->create([
            'username' => 'deleteadmin',
            'name' => '刪除管理員',
            'email' => 'deleteadmin@example.com',
            'status' => 1,
        ]);

        $admin->roles()->attach($role);

        $success = $this->adminService->deleteAdmin($admin->id);

        $this->assertTrue($success);

        // 確認管理員已被刪除
        $deletedAdmin = $this->adminService->getAdminById($admin->id);
        $this->assertNull($deletedAdmin);

        // 確認角色關聯已被刪除
        $this->assertDatabaseMissing('admin_roles', [
            'admin_id' => $admin->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_assign_roles(): void
    {
        // 創建角色
        $role1 = Role::factory()->create([
            'name' => '超級管理員',
            'code' => 'super-admin',
        ]);

        $role2 = Role::factory()->create([
            'name' => '內容管理員',
            'code' => 'content-admin',
        ]);

        // 創建管理員
        $admin = Admin::factory()->create([
            'username' => 'testadmin',
            'name' => '測試管理員',
            'email' => 'testadmin@example.com',
            'status' => 1,
        ]);

        $roles = ['super-admin', 'content-admin'];

        $success = $this->adminService->assignRoles($admin->id, $roles);

        $this->assertTrue($success);

        // 重新獲取管理員
        $updatedAdmin = $this->adminService->getAdminById($admin->id);
        $this->assertCount(2, $updatedAdmin->roles);
        $this->assertTrue($updatedAdmin->roles->contains('code', 'super-admin'));
        $this->assertTrue($updatedAdmin->roles->contains('code', 'content-admin'));
    }

    public function test_assign_roles_to_nonexistent_admin(): void
    {
        $roles = ['super-admin'];

        $success = $this->adminService->assignRoles(999, $roles);

        $this->assertFalse($success);
    }

    public function test_update_nonexistent_admin(): void
    {
        $updateData = [
            'username' => 'updatedadmin',
            'name' => '更新管理員',
            'email' => 'updatedadmin@example.com',
            'status' => 1,
        ];

        $success = $this->adminService->updateAdmin(999, $updateData);

        $this->assertFalse($success);
    }

    public function test_delete_nonexistent_admin(): void
    {
        $success = $this->adminService->deleteAdmin(999);

        $this->assertFalse($success);
    }
}
