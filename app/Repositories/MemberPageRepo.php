<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\MemberPage;
use Illuminate\Database\Eloquent\Collection;

class MemberPageRepo
{
    public function __construct(private MemberPage $memberPage)
    {
    }

    /**
     * 根據 ID 取得會員粉絲頁
     */
    public function getMemberPageById(int $id, array $columns = ['*']): ?MemberPage
    {
        return $this->memberPage->newModelQuery()
            ->where('id', $id)
            ->select($columns)
            ->first();
    }

    /**
     * 根據會員 ID 取得所有粉絲頁
     */
    public function getMemberPagesByMemberId(int $memberId, array $columns = ['*']): Collection
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->select($columns)
            ->get();
    }

    /**
     * 根據會員 ID 和粉絲頁 ID 取得會員粉絲頁
     */
    public function getMemberPageByMemberIdAndPageId(int $memberId, string $pageId, array $columns = ['*']): ?MemberPage
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->where('page_id', $pageId)
            ->select($columns)
            ->first();
    }

    /**
     * 建立新的會員粉絲頁
     */
    public function createMemberPage(array $data): MemberPage
    {
        return $this->memberPage->create($data);
    }

    /**
     * 更新會員粉絲頁
     */
    public function updateMemberPage(int $id, array $data): bool
    {
        return $this->memberPage->newModelQuery()
            ->where('id', $id)
            ->update($data) > 0;
    }

    /**
     * 根據會員 ID 和粉絲頁 ID 更新會員粉絲頁
     */
    public function updateMemberPageByMemberIdAndPageId(int $memberId, string $pageId, array $data): bool
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->where('page_id', $pageId)
            ->update($data) > 0;
    }

    /**
     * 建立或更新會員粉絲頁
     */
    public function upsertMemberPage(array $data): MemberPage
    {
        return $this->memberPage->updateOrCreate(
            [
                'member_id' => $data['member_id'],
                'page_id' => $data['page_id']
            ],
            $data
        );
    }

    /**
     * 刪除會員粉絲頁
     */
    public function deleteMemberPage(int $id): int
    {
        return $this->memberPage->newModelQuery()
            ->where('id', $id)
            ->delete();
    }

    /**
     * 根據會員 ID 刪除所有粉絲頁
     */
    public function deleteMemberPagesByMemberId(int $memberId): int
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->delete();
    }

    /**
     * 根據會員 ID 和粉絲頁 ID 陣列批量刪除會員粉絲頁
     */
    public function deleteMemberPagesByMemberIdAndPageIds(int $memberId, array $pageIds): int
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->whereIn('page_id', $pageIds)
            ->delete();
    }

    /**
     * 檢查會員是否已有特定粉絲頁 ID 的記錄
     */
    public function existsByMemberIdAndPageId(int $memberId, string $pageId): bool
    {
        return $this->memberPage->newModelQuery()
            ->where('member_id', $memberId)
            ->where('page_id', $pageId)
            ->exists();
    }
}
