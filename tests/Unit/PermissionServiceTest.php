<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PermissionService;
use App\Repositories\PermissionRepo;
use App\Models\Permission;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 權限服務測試類別
 */
class PermissionServiceTest extends TestCase
{
    private PermissionService $permissionService;
    private $permissionRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionRepoMock = Mockery::mock(PermissionRepo::class);
        $this->permissionService = new PermissionService($this->permissionRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試獲取權限列表
     */
    public function testGetPermissions(): void
    {
        // 準備測試資料
        $permission1 = new Permission();
        $permission1->id = 1;
        $permission1->name = '公告管理讀取';
        $permission1->code = 'notice:read';
        $permission1->description = '查看公告列表和詳情';
        $permission1->status = 1;
        $permission1->created_at = '2024-01-01 10:00:00';
        $permission1->updated_at = '2024-01-01 10:00:00';

        $permission2 = new Permission();
        $permission2->id = 2;
        $permission2->name = '公告管理編輯';
        $permission2->code = 'notice:edit';
        $permission2->description = '編輯公告內容';
        $permission2->status = 1;
        $permission2->created_at = '2024-01-02 10:00:00';
        $permission2->updated_at = '2024-01-02 10:00:00';

        $paginator = new LengthAwarePaginator(
            collect([$permission1, $permission2]),
            2,
            10,
            1
        );

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('getPermissions')
            ->with([], 1, 10)
            ->once()
            ->andReturn($paginator);

        // 執行測試
        $result = $this->permissionService->getPermissions([], 1, 10);

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

        // 驗證返回的是 Permission 物件
        $this->assertInstanceOf(Permission::class, $result['list'][0]);
        $this->assertEquals('公告管理讀取', $result['list'][0]->name);
        $this->assertEquals('notice:read', $result['list'][0]->code);
    }

    /**
     * 測試獲取所有權限
     */
    public function testGetAllPermissions(): void
    {
        // 準備測試資料
        $permissions = new \Illuminate\Database\Eloquent\Collection([
            new Permission(['id' => 1, 'name' => '權限1', 'code' => 'permission1']),
            new Permission(['id' => 2, 'name' => '權限2', 'code' => 'permission2']),
        ]);

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('getAllPermissions')
            ->once()
            ->andReturn($permissions);

        // 執行測試
        $result = $this->permissionService->getAllPermissions();

        // 驗證結果
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Permission::class, $result[0]);
    }

    /**
     * 測試根據 ID 獲取權限
     */
    public function testFindById(): void
    {
        // 準備測試資料
        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '測試權限';
        $permission->code = 'test-permission';
        $permission->description = '測試權限描述';
        $permission->status = 1;

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($permission);

        // 執行測試
        $result = $this->permissionService->findById(1);

        // 驗證結果
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('測試權限', $result->name);
        $this->assertEquals('test-permission', $result->code);
    }

    /**
     * 測試根據 ID 獲取不存在的權限
     */
    public function testFindByIdNonExistent(): void
    {
        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->permissionService->findById(999);

        // 驗證結果
        $this->assertNull($result);
    }

    /**
     * 測試創建權限
     */
    public function testCreatePermission(): void
    {
        // 準備測試資料
        $permissionData = [
            'name' => '測試權限',
            'code' => 'test-permission',
            'description' => '測試權限描述',
            'status' => 1,
        ];

        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '測試權限';
        $permission->code = 'test-permission';
        $permission->description = '測試權限描述';
        $permission->status = 1;
        $permission->created_at = '2024-01-01 10:00:00';
        $permission->updated_at = '2024-01-01 10:00:00';

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('codeExists')
            ->with('test-permission')
            ->once()
            ->andReturn(false);

        $this->permissionRepoMock->shouldReceive('create')
            ->with($permissionData)
            ->once()
            ->andReturn($permission);

        // 執行測試
        $result = $this->permissionService->create($permissionData);

        // 驗證結果
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('測試權限', $result->name);
        $this->assertEquals('test-permission', $result->code);
    }

    /**
     * 測試創建權限時代碼已存在
     */
    public function testCreatePermissionWithExistingCode(): void
    {
        // 準備測試資料
        $permissionData = [
            'name' => '測試權限',
            'code' => 'existing-permission',
            'description' => '測試權限描述',
            'status' => 1,
        ];

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('codeExists')
            ->with('existing-permission')
            ->once()
            ->andReturn(true);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('權限代碼已存在');

        $this->permissionService->create($permissionData);
    }

    /**
     * 測試更新權限
     */
    public function testUpdatePermission(): void
    {
        // 準備測試資料
        $permissionId = 1;
        $updateData = [
            'name' => '更新權限',
            'code' => 'updated-permission',
            'description' => '更新權限描述',
            'status' => 1,
        ];

        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '更新權限';
        $permission->code = 'updated-permission';
        $permission->description = '更新權限描述';
        $permission->status = 1;

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with($permissionId)
            ->once()
            ->andReturn($permission);

        $this->permissionRepoMock->shouldReceive('codeExists')
            ->with('updated-permission', $permissionId)
            ->once()
            ->andReturn(false);

        $this->permissionRepoMock->shouldReceive('update')
            ->with($permission, $updateData)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->permissionService->update($permissionId, $updateData);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試更新不存在的權限
     */
    public function testUpdateNonExistentPermission(): void
    {
        // 準備測試資料
        $permissionId = 999;
        $updateData = [
            'name' => '更新權限',
            'code' => 'updated-permission',
            'description' => '更新權限描述',
            'status' => 1,
        ];

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with($permissionId)
            ->once()
            ->andReturn(null);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('權限不存在');

        $this->permissionService->update($permissionId, $updateData);
    }

    /**
     * 測試更新權限時代碼已存在
     */
    public function testUpdatePermissionWithExistingCode(): void
    {
        // 準備測試資料
        $permissionId = 1;
        $updateData = [
            'name' => '更新權限',
            'code' => 'existing-permission',
            'description' => '更新權限描述',
            'status' => 1,
        ];

        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '原權限';
        $permission->code = 'original-permission';

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with($permissionId)
            ->once()
            ->andReturn($permission);

        $this->permissionRepoMock->shouldReceive('codeExists')
            ->with('existing-permission', $permissionId)
            ->once()
            ->andReturn(true);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('權限代碼已存在');

        $this->permissionService->update($permissionId, $updateData);
    }

    /**
     * 測試刪除權限
     */
    public function testDeletePermission(): void
    {
        // 準備測試資料
        $permissionId = 1;
        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '測試權限';

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with($permissionId)
            ->once()
            ->andReturn($permission);

        $this->permissionRepoMock->shouldReceive('delete')
            ->with($permission)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->permissionService->delete($permissionId);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除不存在的權限
     */
    public function testDeleteNonExistentPermission(): void
    {
        // 準備測試資料
        $permissionId = 999;

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findById')
            ->with($permissionId)
            ->once()
            ->andReturn(null);

        // 執行測試並驗證異常
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('權限不存在');

        $this->permissionService->delete($permissionId);
    }

    /**
     * 測試檢查權限是否存在
     */
    public function testExists(): void
    {
        // 設定 mock
        $this->permissionRepoMock->shouldReceive('exists')
            ->with(1)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->permissionService->exists(1);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試檢查不存在的權限
     */
    public function testExistsNonExistent(): void
    {
        // 設定 mock
        $this->permissionRepoMock->shouldReceive('exists')
            ->with(999)
            ->once()
            ->andReturn(false);

        // 執行測試
        $result = $this->permissionService->exists(999);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試根據代碼獲取權限
     */
    public function testFindByCode(): void
    {
        // 準備測試資料
        $permission = new Permission();
        $permission->id = 1;
        $permission->name = '測試權限';
        $permission->code = 'test-permission';

        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findByCode')
            ->with('test-permission')
            ->once()
            ->andReturn($permission);

        // 執行測試
        $result = $this->permissionService->findByCode('test-permission');

        // 驗證結果
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test-permission', $result->code);
    }

    /**
     * 測試根據代碼獲取不存在的權限
     */
    public function testFindByCodeNonExistent(): void
    {
        // 設定 mock
        $this->permissionRepoMock->shouldReceive('findByCode')
            ->with('non-existent')
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->permissionService->findByCode('non-existent');

        // 驗證結果
        $this->assertNull($result);
    }
}
