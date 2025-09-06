<?php

namespace App\Foundations\Social;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FB
{
    public const VERSION = 'v23.0';

    /**
     * 用 code 換取 token
     * @return array|null
     * @example [
     *  'access_token' => '...',
     *  'token_type' => 'Bearer',
     *  'expires_in' => 5183999,
     * ]
     */
    public function exchangeCodeForToken(string $code, string $clientId, string $clientSecret, string $redirectUri): ?array
    {
        $tokenUrl = 'https://graph.facebook.com/' . self::VERSION . '/oauth/access_token';

        $form = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'client_secret' => $clientSecret,
            'code' => $code,
        ];

        $apiUrl = $tokenUrl . '?' . http_build_query($form);

        $response = Http::get($apiUrl);
        if ($response->successful()) {
            return $response->json();
        }

        Log::channel('facebook')->error('FB exchange code for token failed', ['response' => $response->body(), 'form' => $form]);

        return null;
    }

    /**
     * 獲取用戶的個人資訊
     */
    public function getUserProfile(string $accessToken): ?array
    {
        $userUrl = 'https://graph.facebook.com/' . self::VERSION . '/me';

        $response = Http::get($userUrl, ['access_token' => $accessToken]);
        if ($response->successful()) {
            return $response->json();
        }

        Log::channel('facebook')->error('FB get user profile failed', ['response' => $response->body()]);

        return null;
    }

    /**
     * 獲取 token 資訊
     */
    public function getTokenInfo(string $token): ?array
    {
        $tokenUrl = 'https://graph.facebook.com/' . self::VERSION . '/debug_token';
        $response = Http::get($tokenUrl, ['input_token' => $token, 'access_token' => config('oauth.fb.client_id')."|".config('oauth.fb.client_secret')]);
        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['data'])) {
                return $responseData['data'];
            }
            return null;
        }

        Log::channel('facebook')->error('FB get token info failed', ['response' => $response->body(), 'input_token' => $token, 'access_token' => config('oauth.fb.client_id')."|".config('oauth.fb.client_secret')]);

        return null;
    }

    /**
     * @Deprecated
     * 獲取用戶的社團
     */
    public function getUserGroups(string $userLongLivedToken): ?array
    {
        $userUrl = 'https://graph.facebook.com/' . self::VERSION . '/me/groups';
        $response = Http::get($userUrl, ['access_token' => $userLongLivedToken]);
        if ($response->successful()) {
            return $response->json();
        }

        Log::channel('facebook')->error('FB get user groups failed', ['response' => $response->body(), 'user_long_lived_token' => $userLongLivedToken]);

        return null;
    }

    /**
     * 獲取用戶的粉絲頁
     */
    public function getUserPages(string $userLongLivedToken): ?array
    {
        $userUrl = 'https://graph.facebook.com/' . self::VERSION . '/me/accounts?fields=id,name,access_token&limit=200';
        $response = Http::get($userUrl, ['access_token' => $userLongLivedToken]);
        if ($response->successful()) {
            return $response->json();
        }

        Log::channel('facebook')->error('FB get user pages failed', ['response' => $response->body(), 'user_long_lived_token' => $userLongLivedToken]);

        return null;
    }

    /**
     * 發布圖片到粉絲頁
     * @return string 圖片 id
     */
    public function uploadImageToPage(string $pageId, string $accessToken, string $imageUrl): string
    {
        $uploadUrl = 'https://graph.facebook.com/' . self::VERSION . '/' . $pageId . '/photos';
        $response = Http::post($uploadUrl, ['access_token' => $accessToken, 'image' => $imageUrl]);
        if ($response->successful()) {
            $result = $response->json();
            return $result['id'];
        }

        Log::channel('facebook')->error('FB upload image to page failed', ['response' => $response->body(), 'page_id' => $pageId, 'access_token' => $accessToken, 'image_url' => $imageUrl]);

        return '';
    }

    /**
     * 發布貼文到粉絲頁
     * @return string 貼文 id
     */
    public function postToPage(string $pageId, string $accessToken, string $message, string $imageId = ''): string
    {
        $postUrl = 'https://graph.facebook.com/' . self::VERSION . '/' . $pageId . '/feed';
        $data = [
            'access_token' => $accessToken,
            'message' => $message,
        ];

        if (!empty($imageId)) {
            $data['object_attachment'] = $imageId;
        }

        $response = Http::post($postUrl, $data);

        if ($response->successful()) {
            $result = $response->json();
            Log::channel('facebook')->info('FB post to page success', [
                'api_url' => $postUrl,
                'request_body' => $data,
                'response' => $result,
                'page_id' => $pageId,
            ]);
            return $result['id'];
        }

        Log::channel('facebook')->error('FB post to page failed', [
            'api_url' => $postUrl,
            'request_body' => $data,
            'response' => $response->body(),
            'page_id' => $pageId,
        ]);

        return '';
    }

    /**
     * 上傳圖片到粉絲頁並發送貼文
     * @return string 貼文 id
     */
    public function uploadImageAndPostToPage(string $pageId, string $accessToken, string $message, string $imageUrl): string
    {
        // 先上傳圖片
        $imageId = $this->uploadImageToPage($pageId, $accessToken, $imageUrl);

        if (empty($imageId)) {
            Log::channel('facebook')->error('FB upload image failed, cannot post', [
                'page_id' => $pageId,
                'image_url' => $imageUrl,
            ]);
            return '';
        }

        // 再發送貼文
        return $this->postToPage($pageId, $accessToken, $message, $imageId);
    }
}
