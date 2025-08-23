<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function success(array $data = [], string $message = 'success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function error(string $message = 'error', int $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [],
        ], $code);
    }
}
