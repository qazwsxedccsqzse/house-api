<?php

namespace App\Services;

use App\Repositories\UserRepo;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private UserRepo $userRepo)
    {

    }

    /**
     * 管理者後台 取得所有用戶
     */
    public function managerGetAllUsers(int $page = 1, int $limit = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->userRepo->getUsersPaginate($page, $limit, $search);
    }
}
