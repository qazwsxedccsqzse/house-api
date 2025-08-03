<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public const ADMIN_NOT_FOUND_MSG = '無此帳號';
    public const ADMIN_PASSWORD_ERROR_MSG = '密碼錯誤';

    public const ADMIN_NOT_FOUND = 1001;
    public const ADMIN_PASSWORD_ERROR = 1002;

    private const MESSAGE = [
        self::ADMIN_NOT_FOUND => self::ADMIN_NOT_FOUND_MSG,
        self::ADMIN_PASSWORD_ERROR => self::ADMIN_PASSWORD_ERROR_MSG,
    ];

    private const STATUS_CODE = [
        self::ADMIN_NOT_FOUND => 404,
        self::ADMIN_PASSWORD_ERROR => 401,
    ];

    public function __construct(int $code, string $message = null, Exception $previous = null)
    {
        $message = $message ?? self::MESSAGE[$code] ?? '未知錯誤';
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return self::STATUS_CODE[$this->getCode()] ?? 400;
    }
}
