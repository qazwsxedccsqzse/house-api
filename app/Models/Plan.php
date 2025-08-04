<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 方案模型
 */
class Plan extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'days',
        'price',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'status' => 'integer',
        'days' => 'integer',
        'price' => 'integer',
    ];

    /**
     * 狀態常數
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * 取得使用此方案的用戶
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * 檢查方案是否為正常狀態
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 取得方案價格（格式化）
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price);
    }
}
