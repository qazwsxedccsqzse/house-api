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
     * 與訂單的關聯
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
