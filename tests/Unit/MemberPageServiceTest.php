<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FbToken;
use App\Models\MemberPage;
use App\Repositories\FbTokenRepo;
use App\Repositories\MemberPageRepo;
use App\Services\FbTokenService;
use App\Services\MemberPageService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class MemberPageServiceTest extends TestCase
{
    private MemberPageService $memberPageService;
    private $memberPageRepoMock;
    private $fbTokenRepoMock;
    private $fbTokenServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock dependencies
        $this->memberPageRepoMock = Mockery::mock(MemberPageRepo::class);
        $this->fbTokenRepoMock = Mockery::mock(FbTokenRepo::class);
        $this->fbTokenServiceMock = Mockery::mock(FbTokenService::class);

        $this->memberPageService = new MemberPageService(
            $this->memberPageRepoMock,
            $this->fbTokenRepoMock,
            $this->fbTokenServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試同步單個會員粉絲頁成功
     */
    public function test_sync_single_member_page_success(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';

        $fbToken = new FbToken();
        $fbToken->access_token = 'test_user_token';

        $userPageTokens = [
            [
                'id' => '123456789',
                'name' => 'Test Page',
                'access_token' => 'test_page_token'
            ]
        ];

        $memberPage = new MemberPage();
        $memberPage->page_id = '123456789';
        $memberPage->page_name = 'Test Page';

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andReturn($fbToken);

        $this->fbTokenServiceMock
            ->shouldReceive('getUserPageTokens')
            ->with('test_user_token')
            ->once()
            ->andReturn($userPageTokens);

        $this->memberPageRepoMock
            ->shouldReceive('upsertMemberPage')
            ->with([
                'member_id' => $memberId,
                'page_id' => '123456789',
                'page_name' => 'Test Page',
                'access_token' => 'test_page_token',
            ])
            ->once()
            ->andReturn($memberPage);

        // 執行測試
        $result = $this->memberPageService->syncMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試同步多個會員粉絲頁成功
     */
    public function test_sync_multiple_member_pages_success(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageIds = ['123456789', '987654321'];

        $fbToken = new FbToken();
        $fbToken->access_token = 'test_user_token';

        $userPageTokens = [
            [
                'id' => '123456789',
                'name' => 'Test Page 1',
                'access_token' => 'test_page_token_1'
            ],
            [
                'id' => '987654321',
                'name' => 'Test Page 2',
                'access_token' => 'test_page_token_2'
            ]
        ];

        $memberPage1 = new MemberPage();
        $memberPage2 = new MemberPage();

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andReturn($fbToken);

        $this->fbTokenServiceMock
            ->shouldReceive('getUserPageTokens')
            ->with('test_user_token')
            ->once()
            ->andReturn($userPageTokens);

        $this->memberPageRepoMock
            ->shouldReceive('upsertMemberPage')
            ->with([
                'member_id' => $memberId,
                'page_id' => '123456789',
                'page_name' => 'Test Page 1',
                'access_token' => 'test_page_token_1',
            ])
            ->once()
            ->andReturn($memberPage1);

        $this->memberPageRepoMock
            ->shouldReceive('upsertMemberPage')
            ->with([
                'member_id' => $memberId,
                'page_id' => '987654321',
                'page_name' => 'Test Page 2',
                'access_token' => 'test_page_token_2',
            ])
            ->once()
            ->andReturn($memberPage2);

        // 執行測試
        $result = $this->memberPageService->syncMemberPages($memberId, $pageIds);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試同步會員粉絲頁失敗 - 找不到 FB Token
     */
    public function test_sync_member_pages_fail_no_fb_token(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageIds = ['123456789'];

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->memberPageService->syncMemberPages($memberId, $pageIds);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試同步會員粉絲頁失敗 - 無法取得粉絲頁資料
     */
    public function test_sync_member_pages_fail_no_page_data(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageIds = ['123456789'];

        $fbToken = new FbToken();
        $fbToken->access_token = 'test_user_token';

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andReturn($fbToken);

        $this->fbTokenServiceMock
            ->shouldReceive('getUserPageTokens')
            ->with('test_user_token')
            ->once()
            ->andReturn([]);

        // 執行測試
        $result = $this->memberPageService->syncMemberPages($memberId, $pageIds);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試同步會員粉絲頁失敗 - 找不到指定粉絲頁
     */
    public function test_sync_member_pages_fail_page_not_found(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageIds = ['123456789', '999999999']; // 999999999 不存在

        $fbToken = new FbToken();
        $fbToken->access_token = 'test_user_token';

        $userPageTokens = [
            [
                'id' => '123456789',
                'name' => 'Test Page 1',
                'access_token' => 'test_page_token_1'
            ]
        ];

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andReturn($fbToken);

        $this->fbTokenServiceMock
            ->shouldReceive('getUserPageTokens')
            ->with('test_user_token')
            ->once()
            ->andReturn($userPageTokens);

        // 執行測試
        $result = $this->memberPageService->syncMemberPages($memberId, $pageIds);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試同步會員粉絲頁失敗 - 發生異常
     */
    public function test_sync_member_pages_fail_exception(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageIds = ['123456789'];

        // Mock expectations
        $this->fbTokenRepoMock
            ->shouldReceive('getFbUserToken')
            ->with($memberId, ['access_token'])
            ->once()
            ->andThrow(new Exception('Database error'));

        // Mock Log facade
        Log::shouldReceive('error')
            ->once()
            ->with(
                '同步會員粉絲頁失敗',
                Mockery::on(function ($data) use ($memberId, $pageIds) {
                    return $data['member_id'] === $memberId &&
                           $data['page_ids'] === $pageIds &&
                           $data['error'] === 'Database error';
                })
            );

        // 執行測試
        $result = $this->memberPageService->syncMemberPages($memberId, $pageIds);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試取得會員的所有粉絲頁
     */
    public function test_get_member_pages(): void
    {
        // 準備測試資料
        $memberId = 1;
        $memberPages = new Collection([
            (object) [
                'id' => 1,
                'page_id' => '123456789',
                'page_name' => 'Test Page 1',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ],
            (object) [
                'id' => 2,
                'page_id' => '987654321',
                'page_name' => 'Test Page 2',
                'created_at' => '2023-01-02 00:00:00',
                'updated_at' => '2023-01-02 00:00:00'
            ]
        ]);

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPagesByMemberId')
            ->with($memberId, ['id', 'page_id', 'page_name', 'created_at', 'updated_at'])
            ->once()
            ->andReturn($memberPages);

        // 執行測試
        $result = $this->memberPageService->getMemberPages($memberId);

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('123456789', $result[0]['page_id']);
        $this->assertEquals('Test Page 1', $result[0]['page_name']);
        $this->assertEquals('987654321', $result[1]['page_id']);
        $this->assertEquals('Test Page 2', $result[1]['page_name']);
    }

    /**
     * 測試取得會員的特定粉絲頁
     */
    public function test_get_member_page(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';
        $memberPage = new MemberPage();
        $memberPage->page_id = '123456789';
        $memberPage->page_name = 'Test Page';

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPageByMemberIdAndPageId')
            ->with($memberId, $pageId)
            ->once()
            ->andReturn($memberPage);

        // 執行測試
        $result = $this->memberPageService->getMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertInstanceOf(MemberPage::class, $result);
        $this->assertEquals('123456789', $result->page_id);
        $this->assertEquals('Test Page', $result->page_name);
    }

    /**
     * 測試取得會員的特定粉絲頁 - 找不到
     */
    public function test_get_member_page_not_found(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPageByMemberIdAndPageId')
            ->with($memberId, $pageId)
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->memberPageService->getMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertNull($result);
    }

    /**
     * 測試刪除會員粉絲頁成功
     */
    public function test_delete_member_page_success(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';
        $memberPage = new MemberPage();
        $memberPage->id = 1;

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPageByMemberIdAndPageId')
            ->with($memberId, $pageId, ['id'])
            ->once()
            ->andReturn($memberPage);

        $this->memberPageRepoMock
            ->shouldReceive('deleteMemberPage')
            ->with(1)
            ->once()
            ->andReturn(1);

        // 執行測試
        $result = $this->memberPageService->deleteMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertTrue($result);
    }

    /**
     * 測試刪除會員粉絲頁失敗 - 找不到記錄
     */
    public function test_delete_member_page_not_found(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPageByMemberIdAndPageId')
            ->with($memberId, $pageId, ['id'])
            ->once()
            ->andReturn(null);

        // 執行測試
        $result = $this->memberPageService->deleteMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertFalse($result);
    }

    /**
     * 測試刪除會員粉絲頁失敗 - 刪除失敗
     */
    public function test_delete_member_page_delete_failed(): void
    {
        // 準備測試資料
        $memberId = 1;
        $pageId = '123456789';
        $memberPage = new MemberPage();
        $memberPage->id = 1;

        // Mock expectations
        $this->memberPageRepoMock
            ->shouldReceive('getMemberPageByMemberIdAndPageId')
            ->with($memberId, $pageId, ['id'])
            ->once()
            ->andReturn($memberPage);

        $this->memberPageRepoMock
            ->shouldReceive('deleteMemberPage')
            ->with(1)
            ->once()
            ->andReturn(0);

        // 執行測試
        $result = $this->memberPageService->deleteMemberPage($memberId, $pageId);

        // 驗證結果
        $this->assertFalse($result);
    }
}