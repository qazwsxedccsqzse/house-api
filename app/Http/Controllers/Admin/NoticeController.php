<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\NoticeService;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * 公告控制器
 */
class NoticeController extends Controller
{
    public function __construct(
        private NoticeService $noticeService
    ) {}

    /**
     * 獲取公告列表
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $data = $this->noticeService->getNotices($page, $limit, $search);

        // 格式化列表資料
        $formattedList = $data['list']->map(function (Notice $notice) {
            return $this->formatForFrontend($notice);
        });

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => [
                'list' => $formattedList->toArray(),
                'total' => $data['total'],
                'page' => $data['page'],
                'limit' => $data['limit'],
            ],
        ]);
    }

    /**
     * 創建公告
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'integer|in:0,1',
        ]);

        $data = $request->only(['title', 'content', 'status']);
        $data['status'] = $data['status'] ?? 1;
        $data['created_by'] = $request->get('admin_id');

        $notice = $this->noticeService->createNotice($data);

        return response()->json([
            'status' => 0,
            'message' => '公告創建成功',
            'data' => $this->formatForFrontend($notice),
        ], 201);
    }

    /**
     * 獲取單個公告
     */
    public function show(int $id): JsonResponse
    {
        $notice = $this->noticeService->getNoticeById($id);

        if (!$notice) {
            return response()->json([
                'status' => -1,
                'message' => '公告不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => $this->formatForFrontend($notice),
        ]);
    }

    /**
     * 更新公告
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'integer|in:0,1',
        ]);

        $data = $request->only(['title', 'content', 'status']);

        $notice = $this->noticeService->updateNotice($id, $data);

        if (!$notice) {
            return response()->json([
                'status' => -1,
                'message' => '公告不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '公告更新成功',
            'data' => $this->formatForFrontend($notice),
        ]);
    }

    /**
     * 刪除公告
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->noticeService->deleteNotice($id);

        if (!$deleted) {
            return response()->json([
                'status' => -1,
                'message' => '公告不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 0,
            'message' => '公告刪除成功',
            'data' => null,
        ]);
    }

    /**
     * 格式化為前端使用的資料格式
     */
    private function formatForFrontend(Notice $notice): array
    {
        return [
            'id' => $notice->id,
            'title' => $notice->title,
            'content' => $notice->content,
            'image' => '', // 目前 migration 沒有 image 欄位，先設為空字串
            'status' => $notice->status,
            'createdAt' => $notice->created_at?->format('Y-m-d H:i:s'),
            'updatedAt' => $notice->updated_at?->format('Y-m-d H:i:s'),
            'createdBy' => $notice->created_by,
        ];
    }
}
