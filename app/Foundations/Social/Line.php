<?php

declare(strict_types=1);

namespace App\Foundations\Social;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Line
{
    /**
     * 交換 authorization code 為 access token
     */
    public function exchangeCodeForToken(string $code, string $codeVerifier, string $clientId, string $clientSecret, string $redirectUri): ?array
    {
        $tokenUrl = 'https://api.line.me/oauth2/v2.1/token';
        
        try {
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code_verifier' => $codeVerifier,
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('LINE token exchange failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('LINE token exchange error', ['message' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 取得用戶 profile 資料
     */
    public function getUserProfile(string $accessToken): ?array
    {
        $userInfoUrl = 'https://api.line.me/v2/profile';
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($userInfoUrl);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('LINE get user profile failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('LINE get user profile error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}