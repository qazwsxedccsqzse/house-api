<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\MemberRepo;
use App\Services\MemberService;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Mockery;

class MemberServiceTest extends TestCase
{
    private MemberService $memberService;
    private $memberRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock MemberRepo
        $this->memberRepoMock = Mockery::mock(MemberRepo::class);
        $this->memberService = new MemberService($this->memberRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試取得所有用戶列表（基本功能）
     */
    public function test_manager_get_all_members_basic(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 20, null)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->memberService->managerGetAllMembers();

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertEquals(20, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
    }

    /**
     * 測試取得所有用戶列表（自訂分頁參數）
     */
    public function test_manager_get_all_members_with_custom_pagination(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            10,
            2
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(2, 10, null)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->memberService->managerGetAllMembers(2, 10);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
    }

    /**
     * 測試取得所有用戶列表（包含搜尋）
     */
    public function test_manager_get_all_members_with_search(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 20, '測試用戶')
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->memberService->managerGetAllMembers(1, 20, '測試用戶');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    /**
     * 測試取得所有用戶列表（空搜尋）
     */
    public function test_manager_get_all_members_with_empty_search(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 20, '')
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->memberService->managerGetAllMembers(1, 20, '');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    /**
     * 測試取得所有用戶列表（使用真實資料）
     */
    public function test_manager_get_all_members_with_real_data(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([
                (object) ['id' => 1, 'name' => '用戶1', 'plan_id' => 1],
                (object) ['id' => 2, 'name' => '用戶2', 'plan_id' => 1],
                (object) ['id' => 3, 'name' => '用戶3', 'plan_id' => 1],
                (object) ['id' => 4, 'name' => '用戶4', 'plan_id' => 1],
                (object) ['id' => 5, 'name' => '用戶5', 'plan_id' => 1],
            ]),
            5,
            10,
            1
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 10, null)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->memberService->managerGetAllMembers(1, 10);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertCount(5, $result->items());
    }

    /**
     * 測試取得所有用戶列表（搜尋功能）
     */
    public function test_manager_get_all_members_search_functionality(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([
                (object) ['id' => 1, 'name' => '張三', 'plan_id' => 1],
            ]),
            1,
            10,
            1
        );

        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 10, '張')
            ->once()
            ->andReturn($expectedPaginator);

        // 執行搜尋測試
        $result = $this->memberService->managerGetAllMembers(1, 10, '張');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
        $this->assertCount(1, $result->items());
        $this->assertEquals('張三', $result->items()[0]->name);
    }

    /**
     * 測試取得所有用戶列表（分頁功能）
     */
    public function test_manager_get_all_members_pagination(): void
    {
        // 準備第一頁測試資料
        $expectedPaginator1 = new LengthAwarePaginator(
            collect([
                (object) ['id' => 1, 'name' => '用戶1'],
                (object) ['id' => 2, 'name' => '用戶2'],
                (object) ['id' => 3, 'name' => '用戶3'],
                (object) ['id' => 4, 'name' => '用戶4'],
                (object) ['id' => 5, 'name' => '用戶5'],
                (object) ['id' => 6, 'name' => '用戶6'],
                (object) ['id' => 7, 'name' => '用戶7'],
                (object) ['id' => 8, 'name' => '用戶8'],
                (object) ['id' => 9, 'name' => '用戶9'],
                (object) ['id' => 10, 'name' => '用戶10'],
            ]),
            25,
            10,
            1
        );

        // 準備第二頁測試資料
        $expectedPaginator2 = new LengthAwarePaginator(
            collect([
                (object) ['id' => 11, 'name' => '用戶11'],
                (object) ['id' => 12, 'name' => '用戶12'],
                (object) ['id' => 13, 'name' => '用戶13'],
                (object) ['id' => 14, 'name' => '用戶14'],
                (object) ['id' => 15, 'name' => '用戶15'],
                (object) ['id' => 16, 'name' => '用戶16'],
                (object) ['id' => 17, 'name' => '用戶17'],
                (object) ['id' => 18, 'name' => '用戶18'],
                (object) ['id' => 19, 'name' => '用戶19'],
                (object) ['id' => 20, 'name' => '用戶20'],
            ]),
            25,
            10,
            2
        );

        // 準備第三頁測試資料
        $expectedPaginator3 = new LengthAwarePaginator(
            collect([
                (object) ['id' => 21, 'name' => '用戶21'],
                (object) ['id' => 22, 'name' => '用戶22'],
                (object) ['id' => 23, 'name' => '用戶23'],
                (object) ['id' => 24, 'name' => '用戶24'],
                (object) ['id' => 25, 'name' => '用戶25'],
            ]),
            25,
            10,
            3
        );

        // Mock MemberRepo 的 getMembersPaginate 方法 - 第一頁
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(1, 10, null)
            ->once()
            ->andReturn($expectedPaginator1);

        // Mock MemberRepo 的 getMembersPaginate 方法 - 第二頁
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(2, 10, null)
            ->once()
            ->andReturn($expectedPaginator2);

        // Mock MemberRepo 的 getMembersPaginate 方法 - 第三頁
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(3, 10, null)
            ->once()
            ->andReturn($expectedPaginator3);

        // 測試第一頁
        $result1 = $this->memberService->managerGetAllMembers(1, 10);
        $this->assertEquals(25, $result1->total());
        $this->assertEquals(10, $result1->perPage());
        $this->assertEquals(1, $result1->currentPage());
        $this->assertCount(10, $result1->items());

        // 測試第二頁
        $result2 = $this->memberService->managerGetAllMembers(2, 10);
        $this->assertEquals(25, $result2->total());
        $this->assertEquals(10, $result2->perPage());
        $this->assertEquals(2, $result2->currentPage());
        $this->assertCount(10, $result2->items());

        // 測試第三頁
        $result3 = $this->memberService->managerGetAllMembers(3, 10);
        $this->assertEquals(25, $result3->total());
        $this->assertEquals(10, $result3->perPage());
        $this->assertEquals(3, $result3->currentPage());
        $this->assertCount(5, $result3->items());
    }

    /**
     * 測試取得所有用戶列表（邊界情況）
     */
    public function test_manager_get_all_members_edge_cases(): void
    {
        // Mock MemberRepo 的 getMembersPaginate 方法
        $this->memberRepoMock
            ->shouldReceive('getMembersPaginate')
            ->with(0, 1, null)
            ->once()
            ->andReturn(new LengthAwarePaginator(collect([]), 0, 1, 0));

        // 測試邊界參數
        $result = $this->memberService->managerGetAllMembers(0, 1);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
