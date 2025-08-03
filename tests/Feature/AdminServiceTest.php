<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\AdminService;
use App\Repositories\AdminRepo;
use App\Exceptions\CustomException;
use App\Foundations\RedisHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminService $adminService;
    private $redisHelper;

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

    public function test_create_admin(): void
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
}
