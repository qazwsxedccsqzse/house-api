<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepo
{
    public function __construct(
        private Order $order
    ) {
    }

    /**
     * 根據會員ID取得訂單列表（分頁）
     */
    public function getOrdersByMemberId(int $memberId, int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        return $this->order->newQuery()
            ->with(['plan'])
            ->byMember($memberId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 根據會員ID取得所有訂單（不分頁）
     */
    public function getAllOrdersByMemberId(int $memberId): Collection
    {
        return $this->order->newQuery()
            ->with(['plan'])
            ->byMember($memberId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 根據會員ID和狀態取得訂單
     */
    public function getOrdersByMemberIdAndStatus(int $memberId, string $status, int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        return $this->order->newQuery()
            ->with(['plan'])
            ->byMember($memberId)
            ->byStatus($status)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 根據ID取得訂單詳情
     */
    public function findById(int $id): ?Order
    {
        return Order::with(['member', 'plan'])->find($id);
    }

    /**
     * 建立新訂單
     */
    public function create(array $data): Order
    {
        return $this->order->create($data);
    }

    /**
     * 更新訂單
     */
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * 刪除訂單
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
