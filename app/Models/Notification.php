<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 管理員通知模型
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * 與模型關聯的資料表名稱
     */
    protected $table = 'admin_notifications';

    /**
     * 可以批量賦值的屬性
     */
    protected $fillable = [
        'type',
        'user_id',
        'message',
        'status',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'type' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 通知類型常數
     */
    const TYPE_SYSTEM = 1;

    /**
     * 通知狀態常數
     */
    const STATUS_UNREAD = 1;
    const STATUS_READ = 2;

    /**
     * 取得通知類型文字
     */
    public function getTypeTextAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SYSTEM => '系統通知',
            default => '未知類型',
        };
    }

    /**
     * 取得通知狀態文字
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UNREAD => '未讀',
            self::STATUS_READ => '已讀',
            default => '未知狀態',
        };
    }

    /**
     * 檢查是否為未讀狀態
     */
    public function isUnread(): bool
    {
        return $this->status === self::STATUS_UNREAD;
    }

    /**
     * 檢查是否為已讀狀態
     */
    public function isRead(): bool
    {
        return $this->status === self::STATUS_READ;
    }
}
