<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RoleService;
use App\Repositories\RoleRepo;
use App\Repositories\PermissionRepo;
use App\Models\Role;
use App\Models\Permission;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 角色服務測試類別
 */
class RoleServiceTest extends TestCase
{
    private RoleService $roleService;
    private $roleRepoMock;
    private $permissionRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleRepoMock = Mockery::mock(RoleRepo::class);
        $this->permissionRepoMock = Mockery::mock(PermissionRepo::class);
        $this->roleService = new RoleService($this->roleRepoMock, $this->permissionRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試獲取角色列表
     */
    public function testGetRoles(): void
    {
        // 準備測試資料
        $role1 = new Role();
        $role1->id = 1;
        $role1->name = '超級管理員';
        $role1->code = 'super-admin';
        $role1->description = '擁有所有權限';
        $role1->status = 1;
        $role1->created_at = '2024-01-01 10:00:00';
        $role1->updated_at = '2024-01-01 10:00:00';

        $role2 = new Role();
        $role2->id = 2;
        $role2->name = '內容管理員';
        $role2->code = 'content-admin';
        $role2->description = '擁有公告相關權限';
        $role2->status = 1;
        $role2->created_at = '2024-01-02 10:00:00';
        $role2->updated_at = '2024-01-02 10:00:00';

        $paginator = new LengthAwarePaginator(
            collect([$role1, $role2]),
            2,
            10,
            1
        );

        // 設定 mock
        $this->roleRepoMock->shouldReceive('getRoles')
            ->with([], 1, 10)
            ->once()
            ->andReturn($paginator);

        // 執行測試
        $result = $this->roleService->getRoles([], 1, 10);

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('limit', $result);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['limit']);
        $this->assertCount(2, $result['list']);

        // 驗證返回的是 Role 物件
        $this->assertInstanceOf(Role::class, $result['list'][0]);
        $this->assertEquals('超級管理員', $result['list'][0]->name);
        $this->assertEquals('super-admin', $result['list'][0]->code);
    }

    /**
     * 測試創建角色
     */
    public function testCreateRole(): void
    {
        // 準備測試資料
        $roleData = [
            'name' => '測試角色',
            'code' => 'test-role',
            'description' => '測試角色描述',
            'status' => 1,
        ];

        $role = new Role();
        $role->id = 1;
        $role->name = '測試角色';
        $role->code = 'test-role';
        $role->description = '測試角色描述';
        $role->status = 1;
        $role->created_at = '2024-01-01 10:00:00';
        $role->updated_at = '2024-01-01 10:00:00';

        // 設定 mock
        $this->roleRepoMock->shouldReceive('codeExists')
            ->with('test-role')
            ->once()
            ->andReturn(false);

        $this->roleRepoMock->shouldReceive('create')
            ->with($roleData)
            ->once()
            ->andReturn($role);

        // 執行測試
        $result = $this->roleService->create($roleData);

        // 驗證結果
        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('測試角色', $result->name);
        $this->assertEquals('test-role', $result->code);
    }

    /**
     * 測試創建角色時代碼已存在
     */
    public function testCreateRoleWithExistingCode(): void
    {
        // 準備測試資料
        $roleData = [
            'name' => '測試角色',
            'code' => 'existing-role',
            'description' => '測試角色描述',
            'status' => 1,
        ];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('codeExists')
            ->with('existing-role')
            ->once()
            ->andReturn(true);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色代碼已存在');

        $this->roleService->create($roleData);
    }

    /**
     * 測試創建角色並分配權限
     */
    public function testCreateRoleWithPermissions(): void
    {
        // 準備測試資料
        $roleData = [
            'name' => '測試角色',
            'code' => 'test-role',
            'description' => '測試角色描述',
            'status' => 1,
            'permissions' => [1, 2, 3],
        ];

        $role = new Role();
        $role->id = 1;
        $role->name = '測試角色';
        $role->code = 'test-role';
        $role->description = '測試角色描述';
        $role->status = 1;

        // 設定 mock
        $this->roleRepoMock->shouldReceive('codeExists')
            ->with('test-role')
            ->once()
            ->andReturn(false);

        $this->roleRepoMock->shouldReceive('create')
            ->with($roleData)
            ->once()
            ->andReturn($role);

        $this->roleRepoMock->shouldReceive('exists')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(2)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(3)
            ->once()
            ->andReturn(true);

        $this->roleRepoMock->shouldReceive('assignPermissions')
            ->with(1, [1, 2, 3])
            ->once();

        // 執行測試
        $result = $this->roleService->create($roleData);

        // 驗證結果
        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals(1, $result->id);
    }

    /**
     * 測試更新角色
     */
    public function testUpdateRole(): void
    {
        // 準備測試資料
        $roleId = 1;
        $updateData = [
            'name' => '更新角色',
            'code' => 'updated-role',
            'description' => '更新角色描述',
            'status' => 1,
        ];

        $role = new Role();
        $role->id = 1;
        $role->name = '更新角色';
        $role->code = 'updated-role';
        $role->description = '更新角色描述';
        $role->status = 1;

        // 設定 mock
        $this->roleRepoMock->shouldReceive('findById')
            ->with($roleId)
            ->once()
            ->andReturn($role);

        $this->roleRepoMock->shouldReceive('codeExists')
            ->with('updated-role', $roleId)
            ->once()
            ->andReturn(false);

        $this->roleRepoMock->shouldReceive('update')
            ->with($role, $updateData)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->roleService->update($roleId, $updateData);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試更新不存在的角色
     */
    public function testUpdateNonExistentRole(): void
    {
        // 準備測試資料
        $roleId = 999;
        $updateData = [
            'name' => '更新角色',
            'code' => 'updated-role',
            'description' => '更新角色描述',
            'status' => 1,
        ];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('findById')
            ->with($roleId)
            ->once()
            ->andReturn(null);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色不存在');

        $this->roleService->update($roleId, $updateData);
    }

    /**
     * 測試更新角色時代碼已存在
     */
    public function testUpdateRoleWithExistingCode(): void
    {
        // 準備測試資料
        $roleId = 1;
        $updateData = [
            'name' => '更新角色',
            'code' => 'existing-role',
            'description' => '更新角色描述',
            'status' => 1,
        ];

        $role = new Role();
        $role->id = 1;
        $role->name = '原角色';
        $role->code = 'original-role';

        // 設定 mock
        $this->roleRepoMock->shouldReceive('findById')
            ->with($roleId)
            ->once()
            ->andReturn($role);

        $this->roleRepoMock->shouldReceive('codeExists')
            ->with('existing-role', $roleId)
            ->once()
            ->andReturn(true);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色代碼已存在');

        $this->roleService->update($roleId, $updateData);
    }

    /**
     * 測試刪除角色
     */
    public function testDeleteRole(): void
    {
        // 準備測試資料
        $roleId = 1;
        $role = new Role();
        $role->id = 1;
        $role->name = '測試角色';

        // 設定 mock
        $this->roleRepoMock->shouldReceive('findById')
            ->with($roleId)
            ->once()
            ->andReturn($role);

        $this->roleRepoMock->shouldReceive('delete')
            ->with($role)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->roleService->delete($roleId);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除不存在的角色
     */
    public function testDeleteNonExistentRole(): void
    {
        // 準備測試資料
        $roleId = 999;

        // 設定 mock
        $this->roleRepoMock->shouldReceive('findById')
            ->with($roleId)
            ->once()
            ->andReturn(null);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色不存在');

        $this->roleService->delete($roleId);
    }

    /**
     * 測試分配權限給角色
     */
    public function testAssignPermissions(): void
    {
        // 準備測試資料
        $roleId = 1;
        $permissionIds = [1, 2, 3];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(2)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(3)
            ->once()
            ->andReturn(true);

        $this->roleRepoMock->shouldReceive('assignPermissions')
            ->with($roleId, $permissionIds)
            ->once();

        // 執行測試
        $this->roleService->assignPermissions($roleId, $permissionIds);

        // 測試成功完成，沒有異常
        $this->assertTrue(true);
    }

    /**
     * 測試分配權限給不存在的角色
     */
    public function testAssignPermissionsToNonExistentRole(): void
    {
        // 準備測試資料
        $roleId = 999;
        $permissionIds = [1, 2, 3];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(false);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色不存在');

        $this->roleService->assignPermissions($roleId, $permissionIds);
    }

    /**
     * 測試分配不存在的權限
     */
    public function testAssignNonExistentPermissions(): void
    {
        // 準備測試資料
        $roleId = 1;
        $permissionIds = [1, 999, 3];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('exists')
            ->with(999)
            ->once()
            ->andReturn(false);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('權限 ID 999 不存在');

        $this->roleService->assignPermissions($roleId, $permissionIds);
    }

    /**
     * 測試獲取角色權限
     */
    public function testGetRolePermissions(): void
    {
        // 準備測試資料
        $roleId = 1;
        $permissions = new \Illuminate\Database\Eloquent\Collection([
            new Permission(['id' => 1, 'name' => '權限1', 'code' => 'permission1']),
            new Permission(['id' => 2, 'name' => '權限2', 'code' => 'permission2']),
        ]);

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(true);

        $this->permissionRepoMock->shouldReceive('getPermissionsByRoleId')
            ->with($roleId)
            ->once()
            ->andReturn($permissions);

        // 執行測試
        $result = $this->roleService->getRolePermissions($roleId);

        // 驗證結果
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Permission::class, $result[0]);
    }

    /**
     * 測試獲取不存在角色的權限
     */
    public function testGetNonExistentRolePermissions(): void
    {
        // 準備測試資料
        $roleId = 999;

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(false);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色不存在');

        $this->roleService->getRolePermissions($roleId);
    }

    /**
     * 測試獲取角色權限 ID 列表
     */
    public function testGetRolePermissionIds(): void
    {
        // 準備測試資料
        $roleId = 1;
        $permissionIds = [1, 2, 3];

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(true);

        $this->roleRepoMock->shouldReceive('getRolePermissionIds')
            ->with($roleId)
            ->once()
            ->andReturn($permissionIds);

        // 執行測試
        $result = $this->roleService->getRolePermissionIds($roleId);

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals([1, 2, 3], $result);
    }

    /**
     * 測試獲取不存在角色的權限 ID 列表
     */
    public function testGetNonExistentRolePermissionIds(): void
    {
        // 準備測試資料
        $roleId = 999;

        // 設定 mock
        $this->roleRepoMock->shouldReceive('exists')
            ->with($roleId)
            ->once()
            ->andReturn(false);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('角色不存在');

        $this->roleService->getRolePermissionIds($roleId);
    }
}
