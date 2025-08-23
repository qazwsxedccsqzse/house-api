<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 用戶模型
 */
class Member extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 指定資料表名稱
     */
    protected $table = 'members';

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'social_id',
        'social_type',
        'social_picture',
        'social_name',
        'plan_id',
    ];

    /**
     * 隱藏的屬性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'integer',
    ];

    /**
     * 狀態常數
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * 社交平台類型常數
     */
    public const SOCIAL_TYPE_LINE = 1;
    public const SOCIAL_TYPE_FACEBOOK = 2;

    /**
     * 與訂單的關聯
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 與方案的關聯
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
