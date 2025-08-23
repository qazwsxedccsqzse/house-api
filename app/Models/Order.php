<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 可填充的欄位
     */
    protected $fillable = [
        'member_id',
        'plan_id',
        'status',
        'price',
        'start_date',
        'end_date',
    ];

    /**
     * 屬性轉換
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * 與會員的關聯
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * 與方案的關聯
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * 查詢指定會員的訂單
     */
    public function scopeByMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * 查詢指定狀態的訂單
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
