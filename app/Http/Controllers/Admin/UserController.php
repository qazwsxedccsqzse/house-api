<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\Admin\UserListRequest;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {

    }

    public function index(UserListRequest $request)
    {
        $data = $request->validated();

        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;
        $search = $data['search'] ?? null;

        $users = $this->userService->managerGetAllUsers(
            page: $page,
            limit: $limit,
            search: $search
        );

        $formattedList = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'estate_broker_number' => $user->estate_broker_number,
                'status' => $user->status,
                'line_id' => $user->line_id,
                'line_picture' => $user->line_picture,
                'plan_id' => $user->plan_id,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 0,
            'message' => '',
            'data' => [
                'list' => $formattedList,
                'total' => $users->total(),
                'page' => $users->currentPage(),
                'limit' => $users->perPage(),
            ],
        ]);
    }
}
