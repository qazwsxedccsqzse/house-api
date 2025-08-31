<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FbToken;
use App\Consts\FBTokenType;
use Illuminate\Database\Eloquent\Collection;

class FbTokenRepo
{
    public function __construct(private FbToken $fbToken)
    {
    }

    /**
     * 根據 ID 取得 Facebook Token
     */
    public function getFbTokenById(int $id, array $columns = ['*']): ?FbToken
    {
        return $this->fbToken->newModelQuery()
            ->where('id', $id)
            ->select($columns)
            ->first();
    }

    /**
     * 根據會員 ID 取得所有 Facebook Token
     */
    public function getFbTokensByMemberId(int $memberId, array $columns = ['*']): Collection
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->select($columns)
            ->get();
    }

    /**
     * 根據會員 ID 和類型取得 Facebook Token
     */
    public function getFbTokensByMemberIdAndType(int $memberId, int $type, array $columns = ['*']): Collection
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->where('type', $type)
            ->select($columns)
            ->get();
    }

    /**
     * 根據 Facebook ID 取得 Facebook Token
     */
    public function getFbTokenByFbId(string $fbId, array $columns = ['*']): ?FbToken
    {
        return $this->fbToken->newModelQuery()
            ->where('fb_id', $fbId)
            ->select($columns)
            ->first();
    }

    /**
     * 根據會員 ID 和 Facebook ID 取得 Facebook Token
     */
    public function getFbTokenByMemberIdAndFbId(int $memberId, string $fbId, array $columns = ['*']): ?FbToken
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->where('fb_id', $fbId)
            ->select($columns)
            ->first();
    }

    /**
     * 取得所有未過期的 Facebook Token
     */
    public function getValidFbTokens(array $columns = ['*']): Collection
    {
        return $this->fbToken->newModelQuery()
            ->where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->select($columns)
            ->get();
    }

    /**
     * 根據會員 ID 取得所有未過期的 Facebook Token
     */
    public function getValidFbTokensByMemberId(int $memberId, array $columns = ['*']): Collection
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->where(function ($query) {
                $query->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
            })
            ->select($columns)
            ->get();
    }

    /**
     * 建立新的 Facebook Token
     */
    public function createFbToken(array $data): FbToken
    {
        return $this->fbToken->create($data);
    }

    public function getFbUserToken(int $memberId, array $columns = ['*']): ?FbToken
    {
        return $this->fbToken->newModelQuery()
            ->select($columns)
            ->where('member_id', $memberId)
            ->where('type', FBTokenType::USER)
            ->first();
    }

    /**
     * 更新 Facebook Token
     */
    public function updateFbToken(int $id, array $data): bool
    {
        return $this->fbToken->newModelQuery()
            ->where('id', $id)
            ->update($data) > 0;
    }

    /**
     * 根據 Facebook ID 更新 Facebook Token
     */
    public function updateFbTokenByFbId(string $fbId, array $data): bool
    {
        return $this->fbToken->newModelQuery()
            ->where('fb_id', $fbId)
            ->update($data) > 0;
    }

    /**
     * 刪除 Facebook Token
     */
    public function deleteFbToken(int $id): int
    {
        return $this->fbToken->newModelQuery()
            ->where('id', $id)
            ->delete();
    }

    /**
     * 根據會員 ID 刪除所有 Facebook Token
     */
    public function deleteFbTokensByMemberId(int $memberId): int
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->delete();
    }

    /**
     * 檢查會員是否已有特定 Facebook ID 的 Token
     */
    public function existsByMemberIdAndFbId(int $memberId, string $fbId): bool
    {
        return $this->fbToken->newModelQuery()
            ->where('member_id', $memberId)
            ->where('fb_id', $fbId)
            ->exists();
    }
}
