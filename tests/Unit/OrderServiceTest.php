<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Order;
use App\Repositories\OrderRepo;
use App\Services\OrderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Mockery;

class OrderServiceTest extends TestCase
{
    private OrderService $orderService;
    private $orderRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock = Mockery::mock(OrderRepo::class);
        $this->orderService = new OrderService($this->orderRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試根據會員ID取得訂單列表
     */
    public function test_get_orders_by_member_id(): void
    {
        $memberId = 1;
        $page = 1;
        $limit = 10;

        // 建立 Mock 分頁物件
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('total')->andReturn(5);
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('perPage')->andReturn(10);

        // 設定 Mock 期望
        $this->orderRepoMock->shouldReceive('getOrdersByMemberId')
            ->once()
            ->with($memberId, $page, $limit)
            ->andReturn($mockPaginator);

        // 執行測試
        $result = $this->orderService->getOrdersByMemberId($memberId, $page, $limit);

        // 驗證結果
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(10, $result->perPage());
    }

    /**
     * 測試分頁參數驗證
     */
    public function test_pagination_parameter_validation(): void
    {
        $memberId = 1;

        // 建立 Mock 分頁物件
        $mockPaginator1 = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator1->shouldReceive('currentPage')->andReturn(1);

        $mockPaginator2 = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator2->shouldReceive('perPage')->andReturn(100);

        // 設定 Mock 期望 - 負數頁碼會被轉換為 1
        $this->orderRepoMock->shouldReceive('getOrdersByMemberId')
            ->once()
            ->with($memberId, 1, 10) // 負數 -1 被轉換為 1
            ->andReturn($mockPaginator1);

        // 設定 Mock 期望 - 超過限制的每頁數量會被限制
        $this->orderRepoMock->shouldReceive('getOrdersByMemberId')
            ->once()
            ->with($memberId, 1, 100) // 200 被限制為 100
            ->andReturn($mockPaginator2);

        // 測試負數頁碼會被轉換為 1
        $result = $this->orderService->getOrdersByMemberId($memberId, -1, 10);
        $this->assertEquals(1, $result->currentPage());

        // 測試超過限制的每頁數量會被限制
        $result = $this->orderService->getOrdersByMemberId($memberId, 1, 200);
        $this->assertEquals(100, $result->perPage());
    }

    /**
     * 測試根據會員ID和狀態取得訂單
     */
    public function test_get_orders_by_member_id_and_status(): void
    {
        $memberId = 1;
        $page = 1;
        $limit = 10;

        // 建立 Mock 分頁物件
        $mockPaginator1 = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator1->shouldReceive('total')->andReturn(3);

        $mockPaginator2 = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator2->shouldReceive('total')->andReturn(2);

        // 設定 Mock 期望 - active 狀態
        $this->orderRepoMock->shouldReceive('getOrdersByMemberIdAndStatus')
            ->once()
            ->with($memberId, 'active', $page, $limit)
            ->andReturn($mockPaginator1);

        // 設定 Mock 期望 - pending 狀態
        $this->orderRepoMock->shouldReceive('getOrdersByMemberIdAndStatus')
            ->once()
            ->with($memberId, 'pending', $page, $limit)
            ->andReturn($mockPaginator2);

        // 測試取得啟用狀態的訂單
        $result = $this->orderService->getOrdersByMemberIdAndStatus($memberId, 'active', $page, $limit);
        $this->assertEquals(3, $result->total());

        // 測試取得待處理狀態的訂單
        $result = $this->orderService->getOrdersByMemberIdAndStatus($memberId, 'pending', $page, $limit);
        $this->assertEquals(2, $result->total());
    }

    /**
     * 測試取得所有訂單（不分頁）
     */
    public function test_get_all_orders_by_member_id(): void
    {
        $memberId = 1;

        // 建立 Mock Collection
        $mockCollection = Mockery::mock(Collection::class);
        $mockCollection->shouldReceive('count')->andReturn(7);

        // 設定 Mock 期望
        $this->orderRepoMock->shouldReceive('getAllOrdersByMemberId')
            ->once()
            ->with($memberId)
            ->andReturn($mockCollection);

        $result = $this->orderService->getAllOrdersByMemberId($memberId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(7, $result->count());
    }

    /**
     * 測試建立訂單
     */
    public function test_create_order(): void
    {
        $orderData = [
            'member_id' => 1,
            'plan_id' => 1,
            'status' => 'pending',
            'price' => 1000.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ];

        // 建立 Mock Order 物件
        $mockOrder = Mockery::mock(Order::class);
        $mockOrder->shouldReceive('getAttribute')->with('member_id')->andReturn(1);
        $mockOrder->shouldReceive('getAttribute')->with('plan_id')->andReturn(1);
        $mockOrder->shouldReceive('getAttribute')->with('status')->andReturn('pending');

        // 設定 Mock 期望
        $this->orderRepoMock->shouldReceive('create')
            ->once()
            ->with($orderData)
            ->andReturn($mockOrder);

        $result = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals(1, $result->member_id);
        $this->assertEquals(1, $result->plan_id);
        $this->assertEquals('pending', $result->status);
    }

    /**
     * 測試更新訂單
     */
    public function test_update_order(): void
    {
        $orderId = 1;
        $updateData = [
            'status' => 'active',
            'price' => 1500.00,
        ];

        // 建立 Mock Order 物件
        $mockOrder = Mockery::mock(Order::class);

        // 設定 Mock 期望
        $this->orderRepoMock->shouldReceive('findById')
            ->once()
            ->with($orderId)
            ->andReturn($mockOrder);

        $this->orderRepoMock->shouldReceive('update')
            ->once()
            ->with($mockOrder, $updateData)
            ->andReturn(true);

        $result = $this->orderService->updateOrder($orderId, $updateData);

        $this->assertTrue($result);
    }

    /**
     * 測試刪除訂單
     */
    public function test_delete_order(): void
    {
        $orderId = 1;

        // 建立 Mock Order 物件
        $mockOrder = Mockery::mock(Order::class);

        // 設定 Mock 期望
        $this->orderRepoMock->shouldReceive('findById')
            ->once()
            ->with($orderId)
            ->andReturn($mockOrder);

        $this->orderRepoMock->shouldReceive('delete')
            ->once()
            ->with($mockOrder)
            ->andReturn(true);

        $result = $this->orderService->deleteOrder($orderId);

        $this->assertTrue($result);
    }

    /**
     * 測試刪除不存在的訂單
     */
    public function test_delete_order_not_found(): void
    {
        $orderId = 999;

        // 設定 Mock 期望 - 找不到訂單
        $this->orderRepoMock->shouldReceive('findById')
            ->once()
            ->with($orderId)
            ->andReturn(null);

        $result = $this->orderService->deleteOrder($orderId);

        $this->assertFalse($result);
    }

    /**
     * 測試更新不存在的訂單
     */
    public function test_update_order_not_found(): void
    {
        $orderId = 999;
        $updateData = ['status' => 'active'];

        // 設定 Mock 期望 - 找不到訂單
        $this->orderRepoMock->shouldReceive('findById')
            ->once()
            ->with($orderId)
            ->andReturn(null);

        $result = $this->orderService->updateOrder($orderId, $updateData);

        $this->assertFalse($result);
    }
}
