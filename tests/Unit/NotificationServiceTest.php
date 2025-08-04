<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Notification;
use App\Repositories\NotificationRepo;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 通知服務測試
 */
class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;
    private NotificationRepo $notificationRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationRepo = new NotificationRepo();
        $this->notificationService = new NotificationService($this->notificationRepo);
    }

    /**
     * 測試取得通知列表
     */
    public function test_get_notifications(): void
    {
        // 建立測試資料
        $notification1 = Notification::factory()->create([
            'type' => 1,
            'user_id' => 1,
            'message' => 'posted a new article 2024 Roadmap',
            'status' => 1,
        ]);

        $notification2 = Notification::factory()->create([
            'type' => 1,
            'user_id' => null,
            'message' => 'System maintenance scheduled',
            'status' => 2,
        ]);

        // 執行測試
        $result = $this->notificationService->getNotifications();

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertArrayHasKey('notifications', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('unread', $result);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['unread']);

        // 驗證通知資料格式
        $notifications = $result['notifications'];
        $this->assertCount(2, $notifications);

        // 驗證第一個通知
        $firstNotification = $notifications[0];
        $this->assertEquals($notification1->id, $firstNotification['id']);
        $this->assertEquals('article', $firstNotification['type']);
        $this->assertEquals('Raymond Pawell', $firstNotification['user']);
        $this->assertEquals('posted a new article 2024 Roadmap', $firstNotification['message']);
        $this->assertTrue($firstNotification['hasAvatar']);
        $this->assertEquals($notification1->created_at->format('Y-m-d H:i:s'), $firstNotification['time']);

        // 驗證第二個通知（系統通知）
        $secondNotification = $notifications[1];
        $this->assertEquals($notification2->id, $secondNotification['id']);
        $this->assertEquals('article', $secondNotification['type']);
        $this->assertEquals('System', $secondNotification['user']);
        $this->assertEquals('System maintenance scheduled', $secondNotification['message']);
        $this->assertFalse($secondNotification['hasAvatar']);
        $this->assertEquals($notification2->created_at->format('Y-m-d H:i:s'), $secondNotification['time']);
    }

    /**
     * 測試時間格式化
     */
    public function test_time_formatting(): void
    {
        // 建立測試資料
        $notification = Notification::factory()->create([
            'created_at' => '2024-01-01 12:30:45',
        ]);

        // 執行測試
        $result = $this->notificationService->getNotifications();

        // 驗證時間格式
        $notifications = $result['notifications'];
        $this->assertEquals('2024-01-01 12:30:45', $notifications[0]['time']);
    }

    /**
     * 測試 hasAvatar 邏輯
     */
    public function test_has_avatar_logic(): void
    {
        // 建立有用戶的通知
        $notificationWithUser = Notification::factory()->create([
            'user_id' => 1,
        ]);

        // 建立系統通知（無用戶）
        $systemNotification = Notification::factory()->create([
            'user_id' => null,
        ]);

        // 執行測試
        $result = $this->notificationService->getNotifications();

        // 驗證 hasAvatar 邏輯
        $notifications = $result['notifications'];

        // 有用戶的通知應該有頭像
        $this->assertTrue($notifications[0]['hasAvatar']);

        // 系統通知應該沒有頭像
        $this->assertFalse($notifications[1]['hasAvatar']);
    }

    /**
     * 測試分頁功能
     */
    public function test_pagination(): void
    {
        // 建立 15 筆測試資料
        Notification::factory()->count(15)->create();

        // 測試第一頁，每頁 10 筆
        $result = $this->notificationService->getNotifications(1, 10);
        $this->assertEquals(15, $result['total']);
        $this->assertCount(10, $result['notifications']);

        // 測試第二頁
        $result = $this->notificationService->getNotifications(2, 10);
        $this->assertEquals(15, $result['total']);
        $this->assertCount(5, $result['notifications']);
    }

    /**
     * 測試標記通知為已讀
     */
    public function test_mark_as_read(): void
    {
        // 建立未讀通知
        $notification = Notification::factory()->unread()->create();

        // 執行測試
        $result = $this->notificationService->markAsRead($notification->id);

        // 驗證結果
        $this->assertTrue($result);

        // 驗證通知狀態已更新
        $notification->refresh();
        $this->assertEquals(Notification::STATUS_READ, $notification->status);
    }

    /**
     * 測試標記所有通知為已讀
     */
    public function test_mark_all_as_read(): void
    {
        // 建立多筆未讀通知
        Notification::factory()->unread()->count(5)->create();
        Notification::factory()->read()->count(3)->create();

        // 執行測試
        $result = $this->notificationService->markAllAsRead();

        // 驗證結果
        $this->assertEquals(5, $result);

        // 驗證所有通知都已標記為已讀
        $unreadCount = Notification::where('status', Notification::STATUS_UNREAD)->count();
        $this->assertEquals(0, $unreadCount);
    }
}
