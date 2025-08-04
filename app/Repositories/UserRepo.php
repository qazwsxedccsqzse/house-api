<?php

namespace App\Repositories;

use App\Models\User;

class UserRepo
{
    public function __construct(private User $user)
    {
    }

    public function getUsersPaginate(int $page = 1, int $limit = 20, ?string $search = null)
    {
        $query = $this->user->query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
