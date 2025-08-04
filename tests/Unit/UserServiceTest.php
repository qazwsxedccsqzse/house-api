<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\Plan;
use App\Repositories\UserRepo;
use App\Services\UserService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Mockery;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private $userRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock UserRepo
        $this->userRepoMock = Mockery::mock(UserRepo::class);
        $this->userService = new UserService($this->userRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試取得所有用戶列表（基本功能）
     */
    public function test_manager_get_all_users_basic(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock UserRepo 的 getUsersPaginate 方法
        $this->userRepoMock
            ->shouldReceive('getUsersPaginate')
            ->with(1, 20, null)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->userService->managerGetAllUsers();

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertEquals(20, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
    }

    /**
     * 測試取得所有用戶列表（自訂分頁參數）
     */
    public function test_manager_get_all_users_with_custom_pagination(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            10,
            2
        );

        // Mock UserRepo 的 getUsersPaginate 方法
        $this->userRepoMock
            ->shouldReceive('getUsersPaginate')
            ->with(2, 10, null)
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->userService->managerGetAllUsers(2, 10);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
    }

    /**
     * 測試取得所有用戶列表（包含搜尋）
     */
    public function test_manager_get_all_users_with_search(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock UserRepo 的 getUsersPaginate 方法
        $this->userRepoMock
            ->shouldReceive('getUsersPaginate')
            ->with(1, 20, '測試用戶')
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->userService->managerGetAllUsers(1, 20, '測試用戶');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    /**
     * 測試取得所有用戶列表（空搜尋）
     */
    public function test_manager_get_all_users_with_empty_search(): void
    {
        // 準備測試資料
        $expectedPaginator = new LengthAwarePaginator(
            collect([]),
            0,
            20,
            1
        );

        // Mock UserRepo 的 getUsersPaginate 方法
        $this->userRepoMock
            ->shouldReceive('getUsersPaginate')
            ->with(1, 20, '')
            ->once()
            ->andReturn($expectedPaginator);

        // 執行測試
        $result = $this->userService->managerGetAllUsers(1, 20, '');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    /**
     * 測試取得所有用戶列表（使用真實資料）
     */
    public function test_manager_get_all_users_with_real_data(): void
    {
        // 建立測試方案
        $plan = Plan::factory()->create([
            'name' => '基本方案',
            'description' => '基本功能方案',
            'days' => 30,
            'price' => 1000,
        ]);

        // 建立測試用戶
        $users = User::factory()->count(5)->create([
            'plan_id' => $plan->id,
            'plan_start_date' => now(),
            'plan_end_date' => now()->addDays(30),
        ]);

        // 建立真實的 UserRepo 和 UserService
        $userRepo = new UserRepo(new User());
        $userService = new UserService($userRepo);

        // 執行測試
        $result = $userService->managerGetAllUsers(1, 10);

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
    public function test_manager_get_all_users_search_functionality(): void
    {
        // 建立測試方案
        $plan = Plan::factory()->create();

        // 建立測試用戶
        User::factory()->create(['name' => '張三']);
        User::factory()->create(['name' => '李四']);
        User::factory()->create(['name' => '王五']);

        // 建立真實的 UserRepo 和 UserService
        $userRepo = new UserRepo(new User());
        $userService = new UserService($userRepo);

        // 執行搜尋測試
        $result = $userService->managerGetAllUsers(1, 10, '張');

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
        $this->assertCount(1, $result->items());
        $this->assertEquals('張三', $result->items()[0]->name);
    }

    /**
     * 測試取得所有用戶列表（分頁功能）
     */
    public function test_manager_get_all_users_pagination(): void
    {
        // 建立測試方案
        $plan = Plan::factory()->create();

        // 建立 25 個測試用戶
        User::factory()->count(25)->create();

        // 建立真實的 UserRepo 和 UserService
        $userRepo = new UserRepo(new User());
        $userService = new UserService($userRepo);

        // 測試第一頁
        $result1 = $userService->managerGetAllUsers(1, 10);
        $this->assertEquals(25, $result1->total());
        $this->assertEquals(10, $result1->perPage());
        $this->assertEquals(1, $result1->currentPage());
        $this->assertCount(10, $result1->items());

        // 測試第二頁
        $result2 = $userService->managerGetAllUsers(2, 10);
        $this->assertEquals(25, $result2->total());
        $this->assertEquals(10, $result2->perPage());
        $this->assertEquals(2, $result2->currentPage());
        $this->assertCount(10, $result2->items());

        // 測試第三頁
        $result3 = $userService->managerGetAllUsers(3, 10);
        $this->assertEquals(25, $result3->total());
        $this->assertEquals(10, $result3->perPage());
        $this->assertEquals(3, $result3->currentPage());
        $this->assertCount(5, $result3->items());
    }

    /**
     * 測試取得所有用戶列表（邊界情況）
     */
    public function test_manager_get_all_users_edge_cases(): void
    {
        // Mock UserRepo 的 getUsersPaginate 方法
        $this->userRepoMock
            ->shouldReceive('getUsersPaginate')
            ->with(0, 1, null)
            ->once()
            ->andReturn(new LengthAwarePaginator(collect([]), 0, 1, 0));

        // 測試邊界參數
        $result = $this->userService->managerGetAllUsers(0, 1);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
