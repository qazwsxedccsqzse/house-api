<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 用戶 Facebook Token 模型
 */
class UserFBToken extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'user_id',
        'token',
        'type',
        'expired_at',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'user_id' => 'integer',
        'type' => 'integer',
        'expired_at' => 'datetime',
    ];

    /**
     * 類型常數
     */
    public const TYPE_PAGE = 1; // 粉專
    public const TYPE_GROUP = 2; // 群組

    /**
     * 取得擁有此 Token 的用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 檢查 Token 是否為粉專類型
     */
    public function isPage(): bool
    {
        return $this->type === self::TYPE_PAGE;
    }

    /**
     * 檢查 Token 是否為群組類型
     */
    public function isGroup(): bool
    {
        return $this->type === self::TYPE_GROUP;
    }

    /**
     * 檢查 Token 是否已過期
     */
    public function isExpired(): bool
    {
        return now()->gt($this->expired_at);
    }

    /**
     * 取得類型名稱
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PAGE => '粉專',
            self::TYPE_GROUP => '群組',
            default => '未知',
        };
    }
}
