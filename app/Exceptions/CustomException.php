<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    // 請登入
    public const UNAUTHORIZED_MSG = '請登入';
    // 管理者帳號相關
    public const ADMIN_NOT_FOUND_MSG = '無此帳號';
    public const ADMIN_PASSWORD_ERROR_MSG = '密碼錯誤';

    // 權限角色相關
    public const PERMISSION_NOT_FOUND_MSG = '無此權限';
    public const PERMISSION_CREATE_FAILED_MSG = '權限新增失敗';
    public const ROLE_NOT_FOUND_MSG = '無此角色';

    // 通用
    public const COMMON_FAILED_MSG = '操作失敗';
    public const VALIDATION_FAILED_MSG = '驗證錯誤';
    public const FILE_UPLOAD_FAILED_MSG = '檔案上傳失敗';

    // 請登入
    public const UNAUTHORIZED = -1;

    // 管理者帳號相關
    public const ADMIN_NOT_FOUND = 1001;
    public const ADMIN_PASSWORD_ERROR = 1002;

    // 權限角色相關
    public const PERMISSION_NOT_FOUND = 2001;
    public const PERMISSION_CREATE_FAILED = 2002;
    public const ROLE_NOT_FOUND = 2003;

    // 通用錯誤
    public const COMMON_FAILED = 3001;
    public const VALIDATION_FAILED = 3002; // 驗證錯誤
    public const FILE_UPLOAD_FAILED = 3003; // 檔案上傳失敗

    private const MESSAGE = [
        self::UNAUTHORIZED => self::UNAUTHORIZED_MSG,

        // 管理者帳號相關
        self::ADMIN_NOT_FOUND => self::ADMIN_NOT_FOUND_MSG,
        self::ADMIN_PASSWORD_ERROR => self::ADMIN_PASSWORD_ERROR_MSG,

        // 權限
        self::PERMISSION_NOT_FOUND => self::PERMISSION_NOT_FOUND_MSG,
        self::PERMISSION_CREATE_FAILED => self::PERMISSION_CREATE_FAILED_MSG,
        self::ROLE_NOT_FOUND => self::ROLE_NOT_FOUND_MSG,

        // 通用
        self::COMMON_FAILED => self::COMMON_FAILED_MSG,
        self::VALIDATION_FAILED => self::VALIDATION_FAILED_MSG,
        self::FILE_UPLOAD_FAILED => self::FILE_UPLOAD_FAILED_MSG,
    ];

    private const STATUS_CODE = [
        self::UNAUTHORIZED => 401,

        self::ADMIN_NOT_FOUND => 404,
        self::ADMIN_PASSWORD_ERROR => 403,

        // 權限角色相關
        self::PERMISSION_NOT_FOUND => 404,
        self::PERMISSION_CREATE_FAILED => 400,
        self::ROLE_NOT_FOUND => 404,

        // 通用
        self::COMMON_FAILED => 500,
        self::VALIDATION_FAILED => 422,
        self::FILE_UPLOAD_FAILED => 400,
    ];

    private int $selfCode;

    public function __construct(int $code, ?string $message = null, ?Exception $previous = null)
    {
        $this->selfCode = $code;
        $message = $message ?? self::MESSAGE[$code] ?? '未知錯誤';
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return self::STATUS_CODE[$this->getCode()] ?? 400;
    }

    public function getSelfCode(): int
    {
        return $this->selfCode;
    }

    public function render()
    {
        return response()->json([
            'status' => $this->getSelfCode(),
            'message' => $this->getMessage(),
            'data' => null,
        ], $this->getStatusCode());
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // ...
    }
}
