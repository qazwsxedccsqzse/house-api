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

    public function getMemberBySocialId(string $socialId, array $columns = ['*'])
    {
        return $this->member->newModelQuery()
            ->where('social_id', $socialId)
            ->select($columns)
            ->first();
    }

    public function getMemberById(int $id, array $columns = ['*']): ?Member
    {
        return $this->member->newModelQuery()
            ->where('id', $id)
            ->select($columns)
            ->first();
    }

    public function getMemberBySocialIdAndSocialType(string $socialId, int $socialType, array $columns = ['*']): ?Member
    {
        return $this->member->newModelQuery()
            ->where('social_id', $socialId)
            ->where('social_type', $socialType)
            ->select($columns)
            ->first();
    }

    public function createMember(array $member)
    {
        return $this->member->create($member);
    }
}
