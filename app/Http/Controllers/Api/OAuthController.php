<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Services\LoginService;
use App\Exceptions\CustomException;
use App\Foundations\Social\Line;
use App\Foundations\Social\FB;
use Illuminate\Support\Facades\Log;
use App\Services\MemberService;
use Illuminate\Support\Facades\Crypt;
use App\Consts\FBTokenType;
use App\Services\FbTokenService;

class OAuthController extends BaseApiController
{
    public function __construct(
        private LoginService $loginService,
        private MemberService $memberService,
        private FbTokenService $fbTokenService,
        private Line $line,
        private FB $fb
    ) {
    }

    /**
     * LINE OAuth callback - 處理 authorization code 並取得 access token
     */
    public function lineOauthCallback(Request $request)
    {
        try {
            // 取得請求參數
            $code = $request->input('code');
            $state = $request->input('state');
            $error = $request->input('error');

            // 檢查是否有錯誤
            if ($error) {
                Log::error('LINE OAuth error', ['error' => $error, 'error_description' => $request->input('error_description')]);
                throw new CustomException(CustomException::COMMON_FAILED, 'LINE OAuth 授權失敗: ' . $error);
            }

            // 檢查必要參數
            if (!$code) {
                throw new CustomException(CustomException::COMMON_FAILED, '缺少 authorization code');
            }

            // 取得設定
            $clientId = config('oauth.line.client_id');
            $clientSecret = config('oauth.line.client_secret');
            // 獲取 token 成功後的 redirect uri
            $redirectUri = config('oauth.line.redirect_uri');

            if (!$clientId || !$clientSecret || !$redirectUri) {
                throw new CustomException(CustomException::COMMON_FAILED, 'LINE 服務設定不完整');
            }

            // 從 state 中取得 code_verifier (假設 state 就是 code_verifier)
            // 實際應用中可能需要更複雜的 state 管理
            $codeVerifier = $this->loginService->getCodeVerifierByState($state);
            if (!$codeVerifier) {
                throw new CustomException(CustomException::COMMON_FAILED, '無效的 code verifier');
            }

            // 交換 access token
            $tokenResponse = $this->line->exchangeCodeForToken($code, $codeVerifier, $clientId, $clientSecret, $redirectUri);
            if (!$tokenResponse) {
                throw new CustomException(CustomException::COMMON_FAILED, '無法取得 access token');
            }

            // 取得用戶資料
            $userProfile = $this->line->getUserProfile($tokenResponse['access_token']);
            if (!$userProfile) {
                throw new CustomException(CustomException::COMMON_FAILED, '無法取得用戶資料');
            }

            // 清除 code verifier
            $this->loginService->getAndDeleteCodeVerifier($codeVerifier);

            // 用戶不存在的話，把用戶寫入到資料庫
            // 存在的話, 直接登入
            if (!$this->memberService->checkMemberSocialId($userProfile['userId'])) {
                $this->memberService->createMember($userProfile);
            }

            $token = $this->loginService->issueAccessTokenBySocialId($userProfile['userId'], 1);
            $appUrl = config('app.url');
            $appDomain = parse_url($appUrl, PHP_URL_HOST);

            return redirect()->away($appUrl . '/signin_handle')
                ->withCookie(cookie()->forever(
                    name: 'app_session',
                    value: $token,
                    secure: true,
                    httpOnly: true,
                    sameSite: 'lax',
                    path: '/',
                    domain: $appDomain
                ));
        } catch (CustomException $e) {
            Log::error('LINE OAuth callback error', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], 400);
        } catch (\Exception $e) {
            Log::error('LINE OAuth callback unexpected error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '系統發生錯誤，請稍後再試',
                'error_code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    /**
     * FB OAuth callback - 處理 authorization code 並取得 access token
     */
    public function fbOauthCallback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $error = $request->input('error');

         // 檢查是否有錯誤
         if ($error) {
            Log::error('FB OAuth error', ['error' => $error, 'error_description' => $request->input('error_description')]);
            throw new CustomException(CustomException::COMMON_FAILED, 'LINE OAuth 授權失敗: ' . $error);
        }

        // 檢查必要參數
        if (!$code) {
            throw new CustomException(CustomException::COMMON_FAILED, '缺少 authorization code');
        }

        // 取得設定
        $clientId = config('oauth.fb.client_id');
        $clientSecret = config('oauth.fb.client_secret');
        // 獲取 token 成功後的 redirect uri
        $redirectUri = config('oauth.fb.redirect_uri');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            throw new CustomException(CustomException::COMMON_FAILED, 'FB 服務設定不完整');
        }

        // decrypt state
        try {
            $frontState = Crypt::decryptString($state);
        } catch (\Exception $e) {
            throw new CustomException(CustomException::COMMON_FAILED, '無效的 state');
        }


        $newState = explode('_', $frontState);
        if (count($newState) !== 2) {
            throw new CustomException(CustomException::COMMON_FAILED, '無效的 state');
        }

        $verifierState = $newState[0];
        $memberId = $newState[1];

        // 從 state 中取得 code_verifier (假設 state 就是 code_verifier)
        // 用來確認 state 是否被篡改
        $codeVerifier = $this->loginService->getCodeVerifierByState($verifierState);
        if (!$codeVerifier) {
            throw new CustomException(CustomException::COMMON_FAILED, '無效的 code verifier');
        }

        // 交換 access token
        $tokenResponse = $this->fb->exchangeCodeForToken($code, $clientId, $clientSecret, $redirectUri);
        if (!$tokenResponse) {
            throw new CustomException(CustomException::COMMON_FAILED, '無法取得 access token');
        }
        Log::info('FB OAuth token response', ['tokenResponse' => $tokenResponse]);

        // 取得用戶資料
        $userProfile = $this->fb->getUserProfile($tokenResponse['access_token']);
        if (!$userProfile) {
            throw new CustomException(CustomException::COMMON_FAILED, '無法取得用戶資料');
        }

        // 清除 code verifier
        $this->loginService->getAndDeleteCodeVerifier($codeVerifier);

        $this->fbTokenService->upsertFbUserToken([
            'member_id' => $memberId,
            'type' => FBTokenType::USER,
            'fb_id' => $userProfile['id'],
            'name' => $userProfile['name'],
            'access_token' => $tokenResponse['access_token'],
            'expires_at' => now()->addSeconds(30 * 24 * 60 * 60)->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->away(config('app.url') . '/platforms');
    }

    /**
     * 為了符合 PKCE
     * @doc: https://developers.line.biz/en/docs/line-login/integrate-pkce/#how-to-integrate-pkce
     *
     * @return void
     */
    public function generateCodeVerifier()
    {
        [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'state' => $state
        ] = $this->loginService->generatePKCE();

        $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge, $state);

        return $this->success([
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'state' => $state,
        ]);
    }

    /**
     * 產生 FB code verifier
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateFBCodeVerifier(Request $request)
    {
        $member = $request->member;
        if (empty($member)) {
            throw new CustomException(CustomException::COMMON_FAILED, '用戶未登入');
        }

        [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'state' => $state
        ] = $this->loginService->generatePKCE();

        $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge, $state);

        $frontState = Crypt::encryptString($state . '_' . $member['id']);

        return $this->success([
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'state' => $frontState,
        ]);
    }
}
