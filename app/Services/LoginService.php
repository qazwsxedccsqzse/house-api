<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\LoginCacheRepo;
use App\Foundations\TokenHelper;
use App\Repositories\MemberRepo;

class LoginService
{
    public function __construct(
        private LoginCacheRepo $loginCacheRepo,
        private MemberRepo $memberRepo,
        private TokenHelper $tokenHelper
    ) {
    }

    /**
     * 產生 code verifier
     *
     * @return string
     */
    public function generatePKCE(): array
    {
        // LINE PKCE 規範：43-128 字符，只能包含 [a-zA-Z0-9\-\._~]
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';
        $length = 43; // 使用最小長度以確保相容性

        $codeVerifier = '';
        for ($i = 0; $i < $length; $i++) {
            $codeVerifier .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $codeChallenge = base64_encode(hash('sha256', $codeVerifier, true));
        $codeChallenge = str_replace('=', '', strtr($codeChallenge, '+/', '-_'));

        // state 使用 xxh3 產生
        $state = hash("xxh3", $codeVerifier, false, [
            'seed' => 1234,
        ]);


        return [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'state' => $state,
        ];
    }

    /**
     * 設定 code verifier
     *
     * @param string $codeVerifier
     * @param string $codeChallenge 透過 code verifier 產生
     * @return bool
     */
    public function setCodeVerifier(string $codeVerifier, string $codeChallenge, string $state): bool
    {
        return $this->loginCacheRepo->setCodeVerifier($codeVerifier, $codeChallenge, $state);
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

    /**
     * 透過 state 取得 code verifier
     *
     * @param string $state
     * @return string|null
     */
    public function getCodeVerifierByState(string $state): ?string
    {
        return $this->loginCacheRepo->getCodeVerifierByState($state);
    }

    /**
     * 核發 access token
     *
     * @param string $codeVerifier
     * @return string|null
     */
    public function issueAccessTokenBySocialId(string $socialId, int $socialType): ?string
    {
        $member = $this->memberRepo->getMemberBySocialIdAndSocialType($socialId, $socialType);
        if (empty($member)) {
            return "";
        }

        $token = $this->tokenHelper->issueAccessToken($member->id);
        // save token to redis
        $this->loginCacheRepo->setAccessToken($member, $token);

        return $token;
    }
}
