<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notice;
use App\Repositories\NoticeRepo;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 公告服務類別
 */
class NoticeService
{
    public function __construct(
        private NoticeRepo $noticeRepo
    ) {}

    /**
     * 獲取公告列表
     */
    public function getNotices(int $page = 1, int $limit = 10, ?string $search = null): array
    {
        $paginator = $this->noticeRepo->getNotices($page, $limit, $search);

        return [
            'list' => $paginator->getCollection(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
        ];
    }

    /**
     * 創建公告
     */
    public function createNotice(array $data): Notice
    {
        return $this->noticeRepo->create($data);
    }

    /**
     * 更新公告
     */
    public function updateNotice(int $id, array $data): ?Notice
    {
        $notice = $this->noticeRepo->findById($id);

        if (!$notice) {
            return null;
        }

        $this->noticeRepo->update($notice, $data);

        // 重新獲取更新後的資料
        return $this->noticeRepo->findById($id);
    }

    /**
     * 刪除公告
     */
    public function deleteNotice(int $id): bool
    {
        return $this->noticeRepo->deleteById($id);
    }

    /**
     * 根據 ID 獲取公告
     */
    public function getNoticeById(int $id): ?Notice
    {
        return $this->noticeRepo->findById($id);
    }

    /**
     * 獲取啟用的公告列表
     */
    public function getActiveNotices(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->noticeRepo->getActiveNotices();
    }
}
