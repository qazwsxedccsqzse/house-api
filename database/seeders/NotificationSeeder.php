<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

/**
 * 通知種子檔案
 */
class NotificationSeeder extends Seeder
{
    /**
     * 執行資料庫種子
     */
    public function run(): void
    {
        // 建立測試通知資料
        $notifications = [
            [
                'type' => 1,
                'user_id' => 1,
                'message' => 'posted a new article 2024 Roadmap',
                'status' => 1,
            ],
            [
                'type' => 1,
                'user_id' => 2,
                'message' => 'commented on your post',
                'status' => 1,
            ],
            [
                'type' => 1,
                'user_id' => null,
                'message' => 'System maintenance scheduled for tomorrow',
                'status' => 2,
            ],
            [
                'type' => 1,
                'user_id' => 3,
                'message' => 'liked your photo',
                'status' => 1,
            ],
            [
                'type' => 1,
                'user_id' => 4,
                'message' => 'shared your post',
                'status' => 2,
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
