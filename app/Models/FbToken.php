<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Facebook Token 模型
 */
class FbToken extends Model
{
    use HasFactory;

    /**
     * 指定資料表名稱
     */
    protected $table = 'fb_tokens';

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'member_id',
        'type',
        'fb_id',
        'name',
        'access_token',
        'expires_at',
    ];

    /**
     * 隱藏的屬性
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'type' => 'integer',
    ];

    /**
     * Token 類型常數
     */
    public const TYPE_PAGE = 1;
    public const TYPE_GROUP = 2;
    public const TYPE_USER = 3;

    /**
     * 與會員的關聯
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
