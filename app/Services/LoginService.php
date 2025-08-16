<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\LoginCacheRepo;

class LoginService
{
    public function __construct(
        private LoginCacheRepo $loginCacheRepo
    ) {
    }

    /**
     * 產生 code verifier
     *
     * @return string
     */
    public function generateCodeVerifier(): string
    {
        // LINE PKCE 規範：43-128 字符，只能包含 [a-zA-Z0-9\-\._~]
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';
        $length = 43; // 使用最小長度以確保相容性
        
        $codeVerifier = '';
        for ($i = 0; $i < $length; $i++) {
            $codeVerifier .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $codeVerifier;
    }

    /**
     * 設定 code verifier
     *
     * @param string $codeVerifier
     * @param string $codeChallenge 透過 code verifier 產生
     * @return bool
     */
    public function setCodeVerifier(string $codeVerifier, string $codeChallenge): bool
    {
        return $this->loginCacheRepo->setCodeVerifier($codeVerifier, $codeChallenge);
    }

    /**
     * 檢查 code verifier 是否存在
     *
     * @param string $codeVerifier
     * @return bool
     */
    public function checkCodeVerifier(string $codeVerifier): bool
    {
        return $this->loginCacheRepo->checkCodeVerifier($codeVerifier);
    }

    /**
     * 取得並刪除 code verifier
     *
     * @param string $codeVerifier
     * @return string|null
     */
    public function getAndDeleteCodeVerifier(string $codeVerifier): ?string
    {
        return $this->loginCacheRepo->getAndDeleteCodeVerifier($codeVerifier);
    }

    public function getCodeVerifierByChallenge(string $codeChallenge): ?string
    {
        return $this->loginCacheRepo->getCodeVerifierByChallenge($codeChallenge);
    }
}