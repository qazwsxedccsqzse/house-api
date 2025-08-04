<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 公告資料庫操作類別
 */
class NoticeRepo
{
    /**
     * 獲取公告列表（分頁）
     */
    public function getNotices(int $page = 1, int $limit = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = Notice::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 獲取所有公告
     */
    public function getAllNotices(): Collection
    {
        return Notice::orderBy('created_at', 'desc')->get();
    }

    /**
     * 根據 ID 獲取公告
     */
    public function findById(int $id): ?Notice
    {
        return Notice::find($id);
    }

    /**
     * 創建公告
     */
    public function create(array $data): Notice
    {
        return Notice::create($data);
    }

    /**
     * 更新公告
     */
    public function update(Notice $notice, array $data): bool
    {
        return $notice->update($data);
    }

    /**
     * 刪除公告
     */
    public function delete(Notice $notice): bool
    {
        return $notice->delete();
    }

    /**
     * 根據 ID 刪除公告
     */
    public function deleteById(int $id): bool
    {
        $notice = $this->findById($id);
        return $notice ? $this->delete($notice) : false;
    }

    /**
     * 獲取啟用的公告列表
     */
    public function getActiveNotices(): Collection
    {
        return Notice::where('status', Notice::STATUS_ENABLE)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
} 