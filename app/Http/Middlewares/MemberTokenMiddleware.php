<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\CustomException;
use App\Repositories\LoginCacheRepo;
use Illuminate\Support\Facades\Log;

class MemberTokenMiddleware
{
    public function __construct(private LoginCacheRepo $loginCacheRepo)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('app_session'); // 已由 EncryptCookies 解密
        Log::info('MemberTokenMiddleware', ['token' => $token]);
        if (!$token) {
            throw new CustomException(CustomException::UNAUTHORIZED);
        }

        $member = $this->loginCacheRepo->getAccessToken($token);
        if (empty($member)) {
            throw new CustomException(CustomException::UNAUTHORIZED);
        }

        $request->merge(['member' => $member]);

        return $next($request);
    }
}
