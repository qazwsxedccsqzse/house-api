<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MemberService;
use App\Http\Requests\Admin\MemberListRequest;

class MemberController extends Controller
{
    public function __construct(private MemberService $memberService)
    {

    }

    public function index(MemberListRequest $request)
    {
        $data = $request->validated();

        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;
        $search = $data['search'] ?? null;

        $members = $this->memberService->managerGetAllMembers(
            page: $page,
            limit: $limit,
            search: $search
        );

        $formattedList = $members->getCollection()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'estate_broker_number' => $member->estate_broker_number,
                'status' => $member->status,
                'line_id' => $member->line_id,
                'line_picture' => $member->line_picture,
                'plan_id' => $member->plan_id,
                'created_at' => $member->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => [
                'list' => $formattedList,
                'total' => $members->total(),
                'page' => $members->currentPage(),
                'limit' => $members->perPage(),
            ],
        ]);
    }
}
