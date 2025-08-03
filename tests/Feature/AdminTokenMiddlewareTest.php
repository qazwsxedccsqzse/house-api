<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Foundations\RedisHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class AdminTokenMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock RedisHelper
        $this->mock(RedisHelper::class, function ($mock) {
            $mock->shouldReceive('get')->andReturn(null);
        });
    }

    public function test_missing_authorization_header(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(403)
            ->assertJson([
                'status' => -1,
                'message' => '請登入',
                'data' => null
            ]);
    }

    public function test_invalid_authorization_format(): void
    {
        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'InvalidFormat token123'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => -1,
                'message' => '請登入',
                'data' => null
            ]);
    }

    public function test_empty_token(): void
    {
        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => -1,
                'message' => '請登入',
                'data' => null
            ]);
    }

    public function test_nonexistent_token(): void
    {
        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer nonexistent_token'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => -1,
                'message' => '請登入',
                'data' => null
            ]);
    }

    public function test_valid_token(): void
    {
        // Mock RedisHelper 返回有效的 admin ID
        $this->mock(RedisHelper::class, function ($mock) {
            $mock->shouldReceive('get')->with('admin:token:valid_token')->andReturn('1');
            $mock->shouldReceive('pipeline')->andReturnUsing(function ($callback) {
                $pipe = Mockery::mock();
                $pipe->shouldReceive('del')->andReturnSelf();
                $callback($pipe);
            });
        });

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer valid_token'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 0,
                'message' => '登出成功',
                'data' => null
            ]);
    }
}
