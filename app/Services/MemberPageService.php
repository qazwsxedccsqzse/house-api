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
use Illuminate\Support\Facades\DB;

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
     * 替換會員的 Facebook 粉絲頁（先刪除舊資料，再寫入新資料）
     */
    public function replaceMemberPages(int $memberId, array $pageIds): bool
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

            // 使用資料庫事務確保資料一致性
            return DB::transaction(function () use ($memberId, $pageIds, $availablePages) {
                // 先刪除該會員的所有現有粉絲頁記錄
                $deletedCount = $this->memberPageRepo->deleteMemberPagesByMemberId($memberId);

                Log::info('刪除會員舊粉絲頁記錄', [
                    'member_id' => $memberId,
                    'deleted_count' => $deletedCount
                ]);

                // 準備批量插入的資料
                $memberPagesData = [];
                foreach ($pageIds as $pageId) {
                    $targetPage = $availablePages[$pageId];
                    $memberPagesData[] = [
                        'member_id' => $memberId,
                        'page_id' => $targetPage['id'],
                        'page_name' => $targetPage['name'],
                        'access_token' => $targetPage['access_token'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 批量建立新的會員粉絲頁記錄
                $this->memberPageRepo->createMemberPages($memberPagesData);

                Log::info('替換會員粉絲頁成功', [
                    'member_id' => $memberId,
                    'page_ids' => $pageIds,
                    'new_count' => count($pageIds)
                ]);

                return true;
            });

        } catch (Exception $e) {
            Log::error('替換會員粉絲頁失敗', [
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


}
