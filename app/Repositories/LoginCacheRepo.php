<?php

namespace App\Repositories;

use App\Foundations\RedisHelper;

class LoginCacheRepo
{
    const CODE_VERIFIER_PREFIX = 'code_verifier:';
    const CODE_CHALLENGE_PREFIX = 'code_challenge:';

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
    public function setCodeVerifier(string $codeVerifier, string $codeChallenge): bool
    {
        $this->redisHelper->pipeline(function ($pipe) use ($codeVerifier, $codeChallenge) {
            $pipe->set(self::CODE_VERIFIER_PREFIX . $codeVerifier, $codeVerifier, 10 * 60);
            $pipe->set(self::CODE_CHALLENGE_PREFIX . $codeChallenge, $codeVerifier, 10 * 60);
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
    public function getCodeVerifierByChallenge(string $codeChallenge): ?string
    {
        return $this->redisHelper->get(self::CODE_CHALLENGE_PREFIX . $codeChallenge);
    }
}