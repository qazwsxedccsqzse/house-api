<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepo;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 通知服務
 */
class NotificationService
{
    public function __construct(
        private NotificationRepo $notificationRepo
    ) {
    }

    /**
     * 取得通知列表
     */
    public function getNotifications(int $page = 1, int $limit = 10): array
    {
        $paginator = $this->notificationRepo->getNotifications($page, $limit);
        $unreadCount = $this->notificationRepo->getUnreadCount();

        $notifications = $paginator->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $this->getTypeText($notification->type),
                'user' => $this->getUserName($notification->user_id),
                'message' => $notification->message,
                'time' => $notification->created_at->format('Y-m-d H:i:s'),
                'hasAvatar' => $this->hasAvatar($notification->user_id),
            ];
        })->toArray();

        return [
            'notifications' => $notifications,
            'total' => $paginator->total(),
            'unread' => $unreadCount,
        ];
    }

    /**
     * 取得通知類型文字
     */
    private function getTypeText(int $type): string
    {
        return match ($type) {
            1 => 'article',
            default => 'system',
        };
    }

    /**
     * 取得用戶名稱
     */
    private function getUserName(?int $userId): string
    {
        if (!$userId) {
            return 'System';
        }

        // 這裡可以根據實際需求從用戶表取得用戶名稱
        // 目前先返回預設值
        return 'Raymond Pawell';
    }

    /**
     * 檢查是否有頭像
     */
    private function hasAvatar(?int $userId): bool
    {
        // 如果有 user_id，表示有用戶，則有頭像
        return $userId !== null;
    }

    /**
     * 標記通知為已讀
     */
    public function markAsRead(int $id): bool
    {
        return $this->notificationRepo->markAsRead($id);
    }

    /**
     * 標記所有通知為已讀
     */
    public function markAllAsRead(): int
    {
        return $this->notificationRepo->markAllAsRead();
    }
}
