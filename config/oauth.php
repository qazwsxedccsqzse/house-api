<?php

return [
    'line' => [
        'client_id' => env('LINE_CLIENT_ID'),
        'client_secret' => env('LINE_CLIENT_SECRET'),
        'redirect_uri' => env('LINE_REDIRECT_URI'), // 獲取 token 成功後的 redirect uri
        'frontend_url' => env('FRONTEND_URL'),
    ],
];
