<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\FbTokenService;

class TokenController extends BaseApiController
{
    public function __construct(
        private FbTokenService $fbTokenService
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
}
