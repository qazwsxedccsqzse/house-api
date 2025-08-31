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

        Log::error('FB exchange code for token failed', ['response' => $response->body(), 'form' => $form]);

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

        Log::error('FB get user profile failed', ['response' => $response->body()]);

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

        Log::error('FB get token info failed', ['response' => $response->body(), 'input_token' => $token, 'access_token' => config('oauth.fb.client_id')."|".config('oauth.fb.client_secret')]);

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

        Log::error('FB get user groups failed', ['response' => $response->body(), 'user_long_lived_token' => $userLongLivedToken]);

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

        Log::error('FB get user pages failed', ['response' => $response->body(), 'user_long_lived_token' => $userLongLivedToken]);

        return null;
    }
}
