<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 公告模型
 */
class Notice extends Model
{
    use HasFactory;

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'title',
        'content',
        'status',
        'created_by',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 狀態常數
     */
    public const STATUS_DISABLE = 0;
    public const STATUS_ENABLE = 1;

    /**
     * 獲取狀態文字
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DISABLE => '停用',
            self::STATUS_ENABLE => '啟用',
            default => '未知',
        };
    }

    /**
     * 獲取建立者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }


}
