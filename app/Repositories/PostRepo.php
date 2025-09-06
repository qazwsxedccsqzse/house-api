<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * 貼文資料存取層
 */
class PostRepo
{
    public function __construct(
        private Post $post
    ) {}
    /**
     * 建立貼文
     */
    public function create(array $data): Post
    {
        return $this->post->create($data);
    }

    /**
     * 根據 ID 取得貼文
     */
    public function findById(int $id): ?Post
    {
        return $this->post->find($id);
    }

    /**
     * 根據會員 ID 取得貼文列表（分頁）
     */
    public function getPostsByMemberId(
        int $memberId,
        int $page = 1,
        int $limit = 10,
        ?int $status = null
    ): LengthAwarePaginator {
        $query = $this->post->newQuery()
            ->where('member_id', $memberId)
            ->with(['member', 'memberPage']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 根據會員 ID 和貼文 ID 取得貼文
     */
    public function findByMemberIdAndId(int $memberId, int $id): ?Post
    {
        return $this->post->newQuery()
            ->where('member_id', $memberId)
            ->where('id', $id)
            ->first();
    }

    /**
     * 更新貼文
     */
    public function update(Post $post, array $data): bool
    {
        return $post->update($data);
    }

    /**
     * 刪除貼文（軟刪除）
     */
    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    /**
     * 永久刪除貼文
     */
    public function forceDelete(Post $post): bool
    {
        return $post->forceDelete();
    }

    /**
     * 根據會員 ID 取得貼文總數
     */
    public function getPostCountByMemberId(int $memberId, ?int $status = null): int
    {
        $query = $this->post->newQuery()
            ->where('member_id', $memberId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    /**
     * 檢查會員是否擁有該粉絲頁
     */
    public function checkMemberPageOwnership(int $memberId, int $pageId): bool
    {
        return $this->post->newQuery()
            ->where('member_id', $memberId)
            ->where('page_id', $pageId)
            ->exists();
    }

    /**
     * 取得排程中的貼文
     */
    public function getScheduledPosts(int $limit = 3): Collection
    {
        return $this->post->newQuery()
            ->where('status', Post::STATUS_SCHEDULED)
            ->where('post_at', '<=', now())
            ->orderBy('post_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * 更新貼文的 post_id 和狀態
     */
    public function updatePostId(Post $post, string $postId, int $status): bool
    {
        return $post->update([
            'post_id' => $postId,
            'status' => $status,
        ]);
    }

    /**
     * 批量更新貼文狀態
     */
    public function updateStatus(array $postIds, int $status): bool
    {
        return $this->post->newQuery()
            ->whereIn('id', $postIds)
            ->where('status', Post::STATUS_SCHEDULED)
            ->update(['status' => $status]) > 0;
    }
}
