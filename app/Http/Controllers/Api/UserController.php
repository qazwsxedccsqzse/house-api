<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Frontend\CommonPageRequest;
use App\Services\MemberService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends BaseApiController
{
    public function __construct(
        private MemberService $memberService,
        private OrderService $orderService
    ) {
    }

    /**
     * 取得用戶資訊
     */
    public function getUserInfo(Request $request)
    {
        $member = $request->member;
        Log::info('getUserInfo', ['member' => $member]);

        return $this->success([
            'member' => $member,
        ]);
    }

    /**
     * 取得用戶訂單
     */
    public function getUserOrders(CommonPageRequest $request)
    {
        $validated = $request->validated();
        /**
         * @var array $member
         */
        $member = $request->member;
        $memberId = intval($member['id']);
        $page = intval($validated['page'] ?? 1);
        $limit = intval($validated['limit'] ?? 15);

        $result = $this->orderService->getOrdersByMemberId(
            memberId: $memberId,
            page: $page,
            limit: $limit
        );

        return $this->success([
            'orders' => $result->items(),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ]
        ]);
    }
}
