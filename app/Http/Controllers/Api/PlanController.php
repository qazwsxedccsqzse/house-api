<?php

namespace App\Http\Controllers\Api;

use App\Services\PlanService;

class PlanController extends BaseApiController
{
    public function __construct(private PlanService $planService)
    {
    }

    /**
     * 取得方案列表
     */
    public function getPlans()
    {
        $plans = $this->planService->getPlans();

        return $this->success(['plans' => $plans]);
    }
}
