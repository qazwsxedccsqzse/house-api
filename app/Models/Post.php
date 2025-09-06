<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 貼文模型
 */
class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 指定資料表名稱
     */
    protected $table = 'posts';

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'member_id',
        'platform',
        'page_id',
        'post_id',
        'post_text',
        'post_image',
        'post_video',
        'status',
        'post_at',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'member_id' => 'integer',
        'platform' => 'integer',
        'page_id' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 平台常數
     */
    public const PLATFORM_FACEBOOK = 1;
    public const PLATFORM_THREAD = 2;

    /**
     * 狀態常數
     */
    public const STATUS_SCHEDULED = 1; // 排程中
    public const STATUS_PUBLISHED = 2; // 已發佈
    public const STATUS_UNPUBLISHED = 3; // 已下架
    public const STATUS_SEND_FAILED = 4; // 發送失敗
    public const STATUS_SENDING = 5; // 發送中

    /**
     * 與會員的關聯
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * 與會員粉絲頁的關聯
     */
    public function memberPage(): BelongsTo
    {
        return $this->belongsTo(MemberPage::class, 'page_id', 'page_id')
            ->where('member_id', $this->member_id);
    }

    /**
     * 取得平台名稱
     */
    public function getPlatformNameAttribute(): string
    {
        return match ($this->platform) {
            self::PLATFORM_FACEBOOK => 'Facebook',
            self::PLATFORM_THREAD => 'Thread',
            default => '未知平台',
        };
    }

    /**
     * 取得狀態名稱
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => '排程中',
            self::STATUS_PUBLISHED => '已發佈',
            self::STATUS_UNPUBLISHED => '已下架',
            self::STATUS_SEND_FAILED => '發送失敗',
            self::STATUS_SENDING => '發送中',
            default => '未知狀態',
        };
    }
}
