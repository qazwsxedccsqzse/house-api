<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Member;

class MemberRepo
{
    public function __construct(private Member $member)
    {
    }

    public function getMembersPaginate(int $page = 1, int $limit = 20, ?string $search = null)
    {
        $query = $this->member->query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
