<?php

return [
    'line' => [
        'client_id' => env('LINE_CLIENT_ID'),
        'client_secret' => env('LINE_CLIENT_SECRET'),
        'redirect_uri' => env('LINE_REDIRECT_URI'), // 獲取 token 成功後的 redirect uri
        'frontend_url' => env('FRONTEND_URL'),
    ],
    'fb' => [
        'client_id' => env('FB_APP_ID'),
        'client_secret' => env('FB_APP_SECRET'),
        'redirect_uri' => env('FB_REDIRECT_URI'), // 獲取 token 成功後的 redirect uri
        'frontend_url' => env('FRONTEND_URL'),
        'app_access_token' => env('FB_APP_ACCESS_TOKEN'),
    ],
];
