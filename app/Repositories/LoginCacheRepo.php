<?php

namespace App\Repositories;

use App\Foundations\RedisHelper;
use App\Models\Member;

class LoginCacheRepo
{
    const CODE_VERIFIER_PREFIX = 'code_verifier:';
    const CODE_CHALLENGE_PREFIX = 'code_challenge:';
    const STATE_PREFIX = 'state:';

    // token 的 cache prefix token => member
    const ACCESS_TOKEN_PREFIX = 't:';
    // 用戶的 access token memberId => token
    const ACCESS_TOKEN_MEMBER_PREFIX = 'tm:';

    const EXPIRE_TIME = 2 * 60; // 2 分鐘
    const TOKEN_EXPIRE_TIME = 24 * 60 * 60; // 24 小時

    public function __construct(private RedisHelper $redisHelper)
    {
    }

    /**
     * 檢查 code verifier 是否存在
     *
     * @param string $codeVerifier
     * @return bool
     */
    public function checkCodeVerifier(string $codeVerifier): bool
    {
        return $this->redisHelper->exists(self::CODE_VERIFIER_PREFIX . $codeVerifier);
    }

    /**
     * 設定 code verifier, 過期時間 10 分鐘
     *
     * @param string $codeVerifier
     * @return bool
     */
    public function setCodeVerifier(string $codeVerifier, string $codeChallenge, string $state): bool
    {
        $this->redisHelper->pipeline(function ($pipe) use ($codeVerifier, $codeChallenge, $state) {
            $pipe->set(self::CODE_VERIFIER_PREFIX . $codeVerifier, $codeVerifier, self::EXPIRE_TIME);
            $pipe->set(self::CODE_CHALLENGE_PREFIX . $codeChallenge, $codeVerifier, self::EXPIRE_TIME);
            $pipe->set(self::STATE_PREFIX . $state, $codeVerifier, self::EXPIRE_TIME);
        });

        return true;
    }

    /**
     * 取得並刪除 code verifier
     *
     * @param string $codeVerifier
     * @return string|null
     */
    public function getAndDeleteCodeVerifier(string $codeVerifier): ?string
    {
        $key = self::CODE_VERIFIER_PREFIX . $codeVerifier;
        $value = $this->redisHelper->get($key);
        if ($value) {
            $this->redisHelper->pipeline(function ($pipe) use ($key, $value) {
                $pipe->del($key);
                $pipe->del(self::CODE_CHALLENGE_PREFIX . $value);
                $pipe->del(self::STATE_PREFIX . $value);
            });

            return $value;
        }

        return null;
    }

    /**
     * 取得 code challenge 透過 code verifier
     *
     * @param string $codeVerifier
     * @return string|null
     */
    public function getCodeVerifierByState(string $state): ?string
    {
        return $this->redisHelper->get(self::STATE_PREFIX . $state);
    }

    /**
     * 設定 access token
     *
     * @param string $memberId
     * @param string $token
     * @return bool
     */
    public function setAccessToken(?Member $member, string $token): bool
    {
        if (!$member) {
            return false;
        }

        $this->redisHelper->pipeline(function ($pipe) use ($member, $token) {
            $pipe->set(self::ACCESS_TOKEN_PREFIX . $token, json_encode($member), self::TOKEN_EXPIRE_TIME);
            $pipe->set(self::ACCESS_TOKEN_MEMBER_PREFIX . $member->id, $token, self::TOKEN_EXPIRE_TIME);
        });

        return true;
    }

    public function getAccessToken(string $token): ?array
    {
        $memberJson = $this->redisHelper->get(self::ACCESS_TOKEN_PREFIX . $token);
        if (!$memberJson) {
            return null;
        }

        return json_decode($memberJson, true);
    }
}
