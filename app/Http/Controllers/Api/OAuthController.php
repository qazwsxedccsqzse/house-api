<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LoginService;
use App\Exceptions\CustomException;
use App\Foundations\Social\Line;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function __construct(
        private LoginService $loginService,
        private Line $line
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
            $codeChallenge = $request->input('code_challenge');
            if (!$codeChallenge) {
                throw new CustomException(CustomException::COMMON_FAILED, '缺少 code_challenge');
            }
            
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
            $codeVerifier = $this->loginService->getCodeVerifierByChallenge($codeChallenge);
            if (!$codeVerifier || !$this->loginService->checkCodeVerifier($codeVerifier)) {
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
            
            // 回傳成功結果
            return response()->json([
                'success' => true,
                'message' => 'LINE 登入成功',
                'data' => [
                    'user_profile' => $userProfile,
                    'access_token' => $tokenResponse['access_token'],
                    'token_type' => $tokenResponse['token_type'] ?? 'Bearer',
                    'expires_in' => $tokenResponse['expires_in'] ?? 3600,
                ]
            ]);
            
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
     * 為了符合 PKCE
     * @doc: https://developers.line.biz/en/docs/line-login/integrate-pkce/#how-to-integrate-pkce
     *
     * @return void
     */
    public function generateCodeVerifier()
    {
        $codeVerifier = $this->loginService->generateCodeVerifier();
        $codeChallenge = base64_encode(hash('sha256', $codeVerifier, true));
        $codeChallenge = str_replace('=', '', strtr($codeChallenge, '+/', '-_'));

        $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge);

        return response()->json([
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
        ]);
    }
}