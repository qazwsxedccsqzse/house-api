<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\AdminService;
use App\Repositories\AdminRepo;
use App\Foundations\RedisHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock RedisHelper 以避免 Redis 連接問題
        $this->mock(RedisHelper::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(false);
            $mock->shouldReceive('pipeline')->andReturnUsing(function ($callback) {
                $pipe = Mockery::mock();
                $pipe->shouldReceive('set')->andReturnSelf();
                $pipe->shouldReceive('expire')->andReturnSelf();
                $callback($pipe);
            });
        });
    }

    public function test_signin_success(): void
    {
        // 建立測試管理員
        $admin = Admin::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/signin', [
            'username' => 'testadmin',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'avatar',
                        'roles',
                        'permissions',
                        'menu'
                    ],
                    'accessToken',
                    'refreshToken'
                ]
            ])
            ->assertJson([
                'status' => 0,
                'message' => '登入成功',
                'data' => [
                    'user' => [
                        'id' => (string) $admin->id,
                        'username' => $admin->username,
                        'email' => $admin->email,
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('data.accessToken'));
        $this->assertNotEmpty($response->json('data.refreshToken'));
    }

    public function test_signin_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/signin', [
            'username' => 'nonexistent',
            'password' => 'password',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 1001,
                'message' => '無此帳號',
                'data' => null
            ]);
    }

    public function test_signin_with_wrong_password(): void
    {
        // 建立測試管理員
        Admin::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/api/auth/signin', [
            'username' => 'testadmin',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 1002,
                'message' => '密碼錯誤',
                'data' => null
            ]);
    }

    public function test_signin_with_missing_username(): void
    {
        $response = $this->postJson('/api/auth/signin', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_signin_with_missing_password(): void
    {
        $response = $this->postJson('/api/auth/signin', [
            'username' => 'testadmin',
        ]);

        $response->assertStatus(422);
    }

    public function test_signin_with_empty_credentials(): void
    {
        $response = $this->postJson('/api/auth/signin', []);

        $response->assertStatus(422);
    }
}
