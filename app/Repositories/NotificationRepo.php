<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 通知儲存庫
 */
class NotificationRepo
{
    /**
     * 取得通知列表（分頁）
     */
    public function getNotifications(int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        return Notification::query()
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 取得所有通知
     */
    public function getAllNotifications(): Collection
    {
        return Notification::query()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 根據 ID 取得通知
     */
    public function findById(int $id): ?Notification
    {
        return Notification::find($id);
    }

    /**
     * 建立通知
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * 更新通知
     */
    public function update(Notification $notification, array $data): bool
    {
        return $notification->update($data);
    }

    /**
     * 刪除通知
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * 取得未讀通知數量
     */
    public function getUnreadCount(): int
    {
        return Notification::where('status', Notification::STATUS_UNREAD)->count();
    }

    /**
     * 標記通知為已讀
     */
    public function markAsRead(int $id): bool
    {
        $notification = $this->findById($id);
        if (!$notification) {
            return false;
        }

        return $notification->update(['status' => Notification::STATUS_READ]);
    }

    /**
     * 標記所有通知為已讀
     */
    public function markAllAsRead(): int
    {
        return Notification::where('status', Notification::STATUS_UNREAD)
            ->update(['status' => Notification::STATUS_READ]);
    }
}
