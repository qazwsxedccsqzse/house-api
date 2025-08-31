<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 會員粉絲頁模型
 */
class MemberPage extends Model
{
    /**
     * 指定資料表名稱
     */
    protected $table = 'member_pages';

    /**
     * 可批量賦值的屬性
     */
    protected $fillable = [
        'member_id',
        'page_id',
        'page_name',
        'access_token',
    ];

    /**
     * 隱藏的屬性
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * 與會員的關聯
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
