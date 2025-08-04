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
        'phone',
        'estate_broker_number',
        'status',
        'line_id',
        'line_picture',
        'plan_id',
        'plan_start_date',
        'plan_end_date',
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
        'plan_start_date' => 'datetime',
        'plan_end_date' => 'datetime',
    ];

    /**
     * 狀態常數
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * 取得用戶的方案
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * 取得用戶的 Facebook Token
     */
    public function fbTokens(): HasMany
    {
        return $this->hasMany(MemberFBToken::class);
    }

    /**
     * 檢查用戶是否為正常狀態
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 檢查用戶是否有有效的方案
     */
    public function hasValidPlan(): bool
    {
        if (!$this->plan_id || !$this->plan_end_date) {
            return false;
        }

        return now()->lt($this->plan_end_date);
    }
}
