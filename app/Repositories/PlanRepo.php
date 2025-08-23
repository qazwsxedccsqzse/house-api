<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

/**
 * 方案儲存庫類別
 */
class PlanRepo
{
    public function __construct(
        private Plan $plan
    ) {
    }

    /**
     * 取得所有啟用的方案
     */
    public function getActivePlans(): Collection
    {
        return $this->plan->newModelQuery()
            ->where('status', Plan::STATUS_ACTIVE)
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * 根據 ID 取得方案
     */
    public function findById(int $id): ?Plan
    {
        return $this->plan->find($id);
    }

    /**
     * 取得所有方案（包含停用的）
     */
    public function getAllPlans(): Collection
    {
        return $this->plan->orderBy('price', 'asc')->get();
    }
}
