<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MemberPage;
use App\Repositories\MemberPageRepo;
use App\Repositories\FbTokenRepo;
use App\Services\FbTokenService;
use App\Consts\FBTokenType;
use Exception;
use Illuminate\Support\Facades\Log;

class MemberPageService
{
    public function __construct(
        private MemberPageRepo $memberPageRepo,
        private FbTokenRepo $fbTokenRepo,
        private FbTokenService $fbTokenService
    ) {
    }

    /**
     * 同步會員的 Facebook 粉絲頁（單個或多個）
     */
    public function syncMemberPages(int $memberId, array $pageIds): bool
    {
        try {
            // 取得會員的 Facebook User Token
            $fbUserToken = $this->fbTokenRepo->getFbUserToken($memberId, ['access_token']);
            if (!$fbUserToken) {
                return false;
            }

            // 從 Facebook API 取得用戶的所有粉絲頁
            $userPageTokens = $this->fbTokenService->getUserPageTokens($fbUserToken->access_token);
            if (empty($userPageTokens)) {
                return false;
            }

            // 建立粉絲頁 ID 對應表
            $availablePages = [];
            foreach ($userPageTokens as $page) {
                $availablePages[$page['id']] = $page;
            }

            // 檢查所有請求的粉絲頁是否都存在
            foreach ($pageIds as $pageId) {
                if (!isset($availablePages[$pageId])) {
                    return false; // 有任何一個粉絲頁找不到就返回失敗
                }
            }

            // 批量建立或更新會員粉絲頁記錄
            foreach ($pageIds as $pageId) {
                $targetPage = $availablePages[$pageId];
                $memberPageData = [
                    'member_id' => $memberId,
                    'page_id' => $targetPage['id'],
                    'page_name' => $targetPage['name'],
                    'access_token' => $targetPage['access_token'],
                ];

                $this->memberPageRepo->upsertMemberPage($memberPageData);
            }

            return true;

        } catch (Exception $e) {
            Log::error('同步會員粉絲頁失敗', [
                'member_id' => $memberId,
                'page_ids' => $pageIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * 同步單個會員 Facebook 粉絲頁（向後相容）
     */
    public function syncMemberPage(int $memberId, string $pageId): bool
    {
        return $this->syncMemberPages($memberId, [$pageId]);
    }

    /**
     * 取得會員的所有粉絲頁
     */
    public function getMemberPages(int $memberId): array
    {
        $memberPages = $this->memberPageRepo->getMemberPagesByMemberId($memberId, [
            'id', 'page_id', 'page_name', 'created_at', 'updated_at'
        ]);

        $pages = $memberPages->map(function ($memberPage) {
            return [
                'id' => $memberPage->id,
                'page_id' => $memberPage->page_id,
                'page_name' => $memberPage->page_name,
                'created_at' => $memberPage->created_at,
                'updated_at' => $memberPage->updated_at,
            ];
        })->toArray();

        return [
            'fan_pages' => $pages,
        ];
    }

    /**
     * 取得會員的特定粉絲頁
     */
    public function getMemberPage(int $memberId, string $pageId): ?MemberPage
    {
        return $this->memberPageRepo->getMemberPageByMemberIdAndPageId($memberId, $pageId);
    }

    /**
     * 批量刪除會員粉絲頁
     */
    public function deleteMemberPages(int $memberId, array $pageIds): bool
    {
        try {
            if (empty($pageIds)) {
                return false;
            }

            // 檢查所有要刪除的粉絲頁是否都屬於該會員
            $existingPages = $this->memberPageRepo->getMemberPagesByMemberId($memberId, ['page_id']);
            $existingPageIds = $existingPages->pluck('page_id')->toArray();

            // 檢查請求的 page_ids 是否都存在於該會員的粉絲頁中
            $invalidPageIds = array_diff($pageIds, $existingPageIds);
            if (!empty($invalidPageIds)) {
                Log::warning('嘗試刪除不存在的粉絲頁', [
                    'member_id' => $memberId,
                    'invalid_page_ids' => $invalidPageIds,
                    'requested_page_ids' => $pageIds
                ]);
                return false;
            }

            // 執行批量刪除
            $deletedCount = $this->memberPageRepo->deleteMemberPagesByMemberIdAndPageIds($memberId, $pageIds);

            Log::info('批量刪除會員粉絲頁成功', [
                'member_id' => $memberId,
                'page_ids' => $pageIds,
                'deleted_count' => $deletedCount
            ]);

            return $deletedCount > 0;

        } catch (Exception $e) {
            Log::error('批量刪除會員粉絲頁失敗', [
                'member_id' => $memberId,
                'page_ids' => $pageIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * 刪除單個會員粉絲頁（向後相容）
     */
    public function deleteMemberPage(int $memberId, string $pageId): bool
    {
        return $this->deleteMemberPages($memberId, [$pageId]);
    }
}
