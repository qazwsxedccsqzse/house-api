<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MemberRepo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberService
{
    public function __construct(private MemberRepo $memberRepo)
    {
    }

    /**
     * [前台] 檢查用戶是否存在
     */
    public function checkMemberSocialId(string $socialId): bool
    {
        $member = $this->memberRepo->getMemberBySocialId($socialId, ['id']);
        return $member ? true : false;
    }

    /**
     * [前台] 建立用戶
     */
    public function createMember(array $userProfile): void
    {
        $member = [
            'social_id' => $userProfile['userId'],
            'social_type' => 1, // 1: LINE, 2: Facebook
            'social_picture' => $userProfile['pictureUrl'],
            'social_name' => $userProfile['displayName'],
            'name' => $userProfile['displayName'],
            'email' => $userProfile['email'] ?? null,
            'password' => Hash::make($userProfile['userId']),
            'status' => 1,
            'plan_id' => 1,
        ];

        $this->memberRepo->createMember($member);
    }

    /**
     * 管理者後台 取得所有用戶
     */
    public function managerGetAllMembers(int $page = 1, int $limit = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->memberRepo->getMembersPaginate($page, $limit, $search);
    }
}
