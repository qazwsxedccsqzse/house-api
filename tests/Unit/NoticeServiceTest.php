<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NoticeService;
use App\Repositories\NoticeRepo;
use App\Models\Notice;
use Mockery;

/**
 * 公告服務測試類別
 */
class NoticeServiceTest extends TestCase
{
    private NoticeService $noticeService;
    private $noticeRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->noticeRepoMock = Mockery::mock(NoticeRepo::class);
        $this->noticeService = new NoticeService($this->noticeRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試獲取公告列表
     */
    public function testGetNotices(): void
    {
        // 準備測試資料
        $notice1 = new Notice();
        $notice1->id = 1;
        $notice1->title = '測試公告1';
        $notice1->content = '測試內容1';
        $notice1->status = 1;
        $notice1->created_by = 'admin1';
        $notice1->created_at = '2024-01-01 10:00:00';
        $notice1->updated_at = '2024-01-01 10:00:00';

        $notice2 = new Notice();
        $notice2->id = 2;
        $notice2->title = '測試公告2';
        $notice2->content = '測試內容2';
        $notice2->status = 1;
        $notice2->created_by = 'admin2';
        $notice2->created_at = '2024-01-02 10:00:00';
        $notice2->updated_at = '2024-01-02 10:00:00';

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([$notice1, $notice2]),
            2,
            10,
            1
        );

        // 設定 mock
        $this->noticeRepoMock->shouldReceive('getNotices')
            ->with(1, 10, null)
            ->once()
            ->andReturn($paginator);

                // 執行測試
        $result = $this->noticeService->getNotices(1, 10);

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('limit', $result);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(10, $result['limit']);
        $this->assertCount(2, $result['list']);

        // 驗證返回的是 Notice 物件集合
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result['list']);
        $this->assertInstanceOf(Notice::class, $result['list'][0]);
        $this->assertEquals('測試公告1', $result['list'][0]->title);
        $this->assertEquals('測試內容1', $result['list'][0]->content);
    }

    /**
     * 測試創建公告
     */
    public function testCreateNotice(): void
    {
        // 準備測試資料
        $noticeData = [
            'title' => '新公告',
            'content' => '新公告內容',
            'status' => 1,
            'created_by' => 'admin1',
        ];

        $createdNotice = new Notice();
        $createdNotice->id = 1;
        $createdNotice->title = '新公告';
        $createdNotice->content = '新公告內容';
        $createdNotice->status = 1;
        $createdNotice->created_by = 'admin1';
        $createdNotice->created_at = '2024-01-01 10:00:00';
        $createdNotice->updated_at = '2024-01-01 10:00:00';

        // 設定 mock
        $this->noticeRepoMock->shouldReceive('create')
            ->with($noticeData)
            ->once()
            ->andReturn($createdNotice);

        // 執行測試
        $result = $this->noticeService->createNotice($noticeData);

        // 驗證結果
        $this->assertInstanceOf(Notice::class, $result);
        $this->assertEquals('新公告', $result->title);
        $this->assertEquals('新公告內容', $result->content);
        $this->assertEquals(1, $result->status);
        $this->assertEquals('admin1', $result->created_by);
    }

    /**
     * 測試更新公告
     */
    public function testUpdateNotice(): void
    {
        // 準備測試資料
        $updateData = [
            'title' => '更新後的公告',
            'content' => '更新後的內容',
            'status' => 0,
        ];

        $existingNotice = new Notice();
        $existingNotice->id = 1;
        $existingNotice->title = '原公告';
        $existingNotice->content = '原內容';
        $existingNotice->status = 1;
        $existingNotice->created_by = 'admin1';
        $existingNotice->created_at = '2024-01-01 10:00:00';
        $existingNotice->updated_at = '2024-01-01 10:00:00';

        $updatedNotice = new Notice();
        $updatedNotice->id = 1;
        $updatedNotice->title = '更新後的公告';
        $updatedNotice->content = '更新後的內容';
        $updatedNotice->status = 0;
        $updatedNotice->created_by = 'admin1';
        $updatedNotice->created_at = '2024-01-01 10:00:00';
        $updatedNotice->updated_at = '2024-01-02 10:00:00';

        // 設定 mock
        $this->noticeRepoMock->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($existingNotice);

        $this->noticeRepoMock->shouldReceive('update')
            ->with($existingNotice, $updateData)
            ->once()
            ->andReturn(true);

        $this->noticeRepoMock->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($updatedNotice);

        // 執行測試
        $result = $this->noticeService->updateNotice(1, $updateData);

        // 驗證結果
        $this->assertInstanceOf(Notice::class, $result);
        $this->assertEquals('更新後的公告', $result->title);
        $this->assertEquals('更新後的內容', $result->content);
        $this->assertEquals(0, $result->status);
    }

    /**
     * 測試更新不存在的公告
     */
    public function testUpdateNonExistentNotice(): void
    {
        // 設定 mock
        $this->noticeRepoMock->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->noticeService->updateNotice(999, ['title' => 'test']);

        // 驗證結果
        $this->assertNull($result);
    }

    /**
     * 測試刪除公告
     */
    public function testDeleteNotice(): void
    {
        // 設定 mock
        $this->noticeRepoMock->shouldReceive('deleteById')
            ->with(1)
            ->once()
            ->andReturn(true);

        // 執行測試
        $result = $this->noticeService->deleteNotice(1);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除不存在的公告
     */
    public function testDeleteNonExistentNotice(): void
    {
        // 設定 mock
        $this->noticeRepoMock->shouldReceive('deleteById')
            ->with(999)
            ->once()
            ->andReturn(false);

        // 執行測試
        $result = $this->noticeService->deleteNotice(999);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試根據 ID 獲取公告
     */
    public function testGetNoticeById(): void
    {
        // 準備測試資料
        $notice = new Notice();
        $notice->id = 1;
        $notice->title = '測試公告';
        $notice->content = '測試內容';
        $notice->status = 1;
        $notice->created_by = 'admin1';
        $notice->created_at = '2024-01-01 10:00:00';
        $notice->updated_at = '2024-01-01 10:00:00';

        // 設定 mock
        $this->noticeRepoMock->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($notice);

        // 執行測試
        $result = $this->noticeService->getNoticeById(1);

        // 驗證結果
        $this->assertInstanceOf(Notice::class, $result);
        $this->assertEquals('測試公告', $result->title);
        $this->assertEquals('測試內容', $result->content);
    }

    /**
     * 測試獲取不存在的公告
     */
    public function testGetNonExistentNoticeById(): void
    {
        // 設定 mock
        $this->noticeRepoMock->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->noticeService->getNoticeById(999);

        // 驗證結果
        $this->assertNull($result);
    }

    /**
     * 測試獲取啟用的公告列表
     */
    public function testGetActiveNotices(): void
    {
        // 準備測試資料
        $notice1 = new Notice();
        $notice1->id = 1;
        $notice1->title = '啟用公告1';
        $notice1->content = '內容1';
        $notice1->status = 1;
        $notice1->created_by = 'admin1';
        $notice1->created_at = '2024-01-01 10:00:00';
        $notice1->updated_at = '2024-01-01 10:00:00';

        $notice2 = new Notice();
        $notice2->id = 2;
        $notice2->title = '啟用公告2';
        $notice2->content = '內容2';
        $notice2->status = 1;
        $notice2->created_by = 'admin2';
        $notice2->created_at = '2024-01-02 10:00:00';
        $notice2->updated_at = '2024-01-02 10:00:00';

        // 設定 mock
        $this->noticeRepoMock->shouldReceive('getActiveNotices')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$notice1, $notice2]));

                // 執行測試
        $result = $this->noticeService->getActiveNotices();

        // 驗證結果
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertCount(2, $result);

        $this->assertEquals('啟用公告1', $result[0]->title);
        $this->assertEquals(1, $result[0]->status);

        $this->assertEquals('啟用公告2', $result[1]->title);
        $this->assertEquals(1, $result[1]->status);
    }
}
