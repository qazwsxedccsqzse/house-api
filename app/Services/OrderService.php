<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    public function __construct(private OrderRepo $orderRepo)
    {
    }

    /**
     * 根據會員ID取得訂單列表（分頁）
     */
    public function getOrdersByMemberId(int $memberId, int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        // 驗證分頁參數
        $page = max(1, $page);
        $limit = max(1, min(100, $limit)); // 限制每頁最多100筆

        return $this->orderRepo->getOrdersByMemberId($memberId, $page, $limit);
    }

    /**
     * 根據會員ID取得所有訂單（不分頁）
     */
    public function getAllOrdersByMemberId(int $memberId): Collection
    {
        return $this->orderRepo->getAllOrdersByMemberId($memberId);
    }

    /**
     * 根據會員ID和狀態取得訂單
     */
    public function getOrdersByMemberIdAndStatus(int $memberId, string $status, int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        // 驗證分頁參數
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        return $this->orderRepo->getOrdersByMemberIdAndStatus($memberId, $status, $page, $limit);
    }

    /**
     * 根據ID取得訂單詳情
     */
    public function getOrderById(int $id): ?object
    {
        return $this->orderRepo->findById($id);
    }

    /**
     * 建立新訂單
     */
    public function createOrder(array $data): object
    {
        // 這裡可以加入業務邏輯驗證
        // 例如：檢查會員是否存在、方案是否有效等
        
        return $this->orderRepo->create($data);
    }

    /**
     * 更新訂單
     */
    public function updateOrder(int $orderId, array $data): bool
    {
        $order = $this->orderRepo->findById($orderId);
        
        if (!$order) {
            return false;
        }

        return $this->orderRepo->update($order, $data);
    }

    /**
     * 刪除訂單
     */
    public function deleteOrder(int $orderId): bool
    {
        $order = $this->orderRepo->findById($orderId);
        
        if (!$order) {
            return false;
        }

        return $this->orderRepo->delete($order);
    }
}
