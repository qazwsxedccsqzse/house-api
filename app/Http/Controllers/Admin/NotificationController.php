<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationListRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

/**
 * 通知控制器
 */
class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * 取得通知列表
     */
    public function index(NotificationListRequest $request): JsonResponse
    {
        $data = $request->validated();
        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;

        $data = $this->notificationService->getNotifications($page, $limit);

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $data,
        ]);
    }

    /**
     * 標記通知為已讀
     */
    public function markAsRead(int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id);

        if (!$success) {
            return response()->json([
                'status' => -1,
                'message' => '通知不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Marked as read successfully',
            'data' => null,
        ]);
    }

    /**
     * 標記所有通知為已讀
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead();

        return response()->json([
            'status' => 0,
            'message' => 'All notifications marked as read',
            'data' => null,
        ]);
    }
}
