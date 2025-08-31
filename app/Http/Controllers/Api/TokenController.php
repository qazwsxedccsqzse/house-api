<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\FbTokenService;
use App\Services\MemberPageService;
use Illuminate\Http\JsonResponse;

class TokenController extends BaseApiController
{
    public function __construct(
        private FbTokenService $fbTokenService,
        private MemberPageService $memberPageService
    ) {
    }

    /**
     * 取得用戶 FB 的 User Long lived Token
     */
    public function getUserToken(Request $request)
    {
        $member = $request->member;
        $token = $this->fbTokenService->getFbUserToken($member['id']);
        if (!$token) {
            return $this->success([
                'token' => null,
                'info' => null,
            ]);
        }

        $tokenInfo = $this->fbTokenService->getTokenInfo($token->access_token);
        return $this->success([
            'token' => $token,
            'info' => $tokenInfo,
        ]);
    }

    /**
     * 取得 token 過期時間
     */
    public function getUserTokenExpiresTime(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return $this->success([
                'expires_time' => null,
            ]);
        }

        $tokenInfo = $this->fbTokenService->getTokenInfo($token);
        return $this->success([
            'expires_time' => $tokenInfo['expires_at'],
        ]);
    }

    /**
     * 同步會員的 Facebook 粉絲頁
     */
    public function syncMemberPage(Request $request): JsonResponse
    {
        $pageId = $request->input('page_id');
        $pageIds = $request->input('page_ids');

        // 驗證輸入
        if (!$pageId && !$pageIds) {
            return $this->error('請提供 page_id 或 page_ids', 400);
        }

        if ($pageId && $pageIds) {
            return $this->error('請只提供 page_id 或 page_ids 其中一個', 400);
        }

        // 統一處理為陣列格式
        $targetPageIds = $pageIds ?: [$pageId];
        
        // 驗證陣列格式
        if (!is_array($targetPageIds) || empty($targetPageIds)) {
            return $this->error('page_ids 必須是非空陣列', 400);
        }

        $member = $request->member;
        $success = $this->memberPageService->syncMemberPages($member['id'], $targetPageIds);

        if ($success) {
            return $this->success([]);
        } else {
            return $this->error('同步粉絲頁失敗', 400);
        }
    }

    /**
     * 取得會員存入的粉絲頁列表
     */
    public function getMemberPages(Request $request): JsonResponse
    {
        $member = $request->member;
        $memberPages = $this->memberPageService->getMemberPages($member['id']);

        return $this->success($memberPages);
    }
}
