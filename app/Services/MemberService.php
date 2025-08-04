<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MemberRepo;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberService
{
    public function __construct(private MemberRepo $memberRepo)
    {
    }

    /**
     * 管理者後台 取得所有用戶
     */
    public function managerGetAllMembers(int $page = 1, int $limit = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->memberRepo->getMembersPaginate($page, $limit, $search);
    }
}
