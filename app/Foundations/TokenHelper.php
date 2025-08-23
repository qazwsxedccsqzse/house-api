<?php
namespace App\Foundations;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class TokenHelper
{
    public function __construct()
    {
    }

    /**
     * 核發 access token
     */
    public function issueAccessToken(string $memberId): string
    {
        $now = Carbon::now()->tz(config('app.timezone'))->timestamp;
        $token = Crypt::encryptString($memberId . "|" . $now);
        return $token;
    }

    /**
     * 驗證 access token
     */
    public function verifyAccessToken(string $token): bool
    {
        $decrypted = Crypt::decryptString($token);
        $parts = explode("|", $decrypted);
        if (count($parts) !== 2) {
            return false;
        }

        $memberId = $parts[0];
        $timestamp = $parts[1];

        return is_numeric($memberId) && is_numeric($timestamp);
    }
}
