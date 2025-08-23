<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Repositories\PlanRepo;

/**
 * 方案服務類別
 */
class PlanService
{
    public function __construct(private PlanRepo $planRepo)
    {
    }

    /**
     * 取得所有啟用的方案
     */
    public function getPlans(): array
    {
        $plans = $this->planRepo->getActivePlans();

        return $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => explode("\n", $plan->description),
                'days' => $plan->days,
                'price' => $plan->price,
                'annual_price' => $plan->annual_price,
            ];
        })->toArray();
    }
}
