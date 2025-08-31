<?php

namespace App\Services;

use App\Models\FbToken;
use App\Repositories\FbTokenRepo;
use App\Foundations\Social\FB;

class FbTokenService
{
    public function __construct(
        private FbTokenRepo $fbTokenRepo,
        private FB $fb
    ) {
    }

    public function createFbToken(array $data): FbToken
    {
        return $this->fbTokenRepo->createFbToken($data);
    }

    public function upsertFbUserToken(array $data): bool
    {
        $memberId = $data['member_id'];

        $userToken = $this->fbTokenRepo->getFbUserToken($memberId, ['id']);
        if ($userToken) {
            $updateData = [
                'access_token' => $data['access_token'],
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
            $this->fbTokenRepo->updateFbToken($userToken->id, $updateData);
            return true;
        }

        $this->createFbToken($data);
        return true;
    }

    public function getFbUserToken(int $memberId, array $columns = ['*']): ?FbToken
    {
        return $this->fbTokenRepo->getFbUserToken($memberId, $columns);
    }

    public function getTokenInfo(string $token): ?array
    {
        $tokenInfo = $this->fb->getTokenInfo($token);
        if (!$tokenInfo) {
            return null;
        }

        return $tokenInfo;
    }

    public function getUserPageTokens(string $userLongLivedToken): array
    {
        $userPagesResponse = $this->fb->getUserPages($userLongLivedToken);
        if (!$userPagesResponse) {
            return [];
        }

        if (count($userPagesResponse['data']) === 0) {
            return [];
        }

        $userPageTokens = [];
        foreach ($userPagesResponse['data'] as $userPage) {
            $userPageTokens[] = [
                'id' => $userPage['id'],
                'name' => $userPage['name'],
                'access_token' => $userPage['access_token'],
            ];
        }

        return $userPageTokens;
    }
}
