<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LoginService;
use App\Repositories\LoginCacheRepo;
use App\Repositories\MemberRepo;
use App\Foundations\TokenHelper;
use App\Models\Member;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LoginServiceTest extends TestCase
{
    private LoginService $loginService;
    private MockObject|LoginCacheRepo $loginCacheRepoMock;
    private MockObject|MemberRepo $memberRepoMock;
    private MockObject|TokenHelper $tokenHelperMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 建立所有必要的 mocks
        $this->loginCacheRepoMock = $this->createMock(LoginCacheRepo::class);
        $this->memberRepoMock = $this->createMock(MemberRepo::class);
        $this->tokenHelperMock = $this->createMock(TokenHelper::class);

        // 建立 LoginService 實例，注入所有依賴
        $this->loginService = new LoginService(
            $this->loginCacheRepoMock,
            $this->memberRepoMock,
            $this->tokenHelperMock
        );
    }

    /**
     * 測試 generatePKCE 方法
     */
    public function test_generate_pkce_returns_valid_data(): void
    {
        $result = $this->loginService->generatePKCE();

        // 驗證回傳是陣列
        $this->assertIsArray($result);

        // 驗證必要的鍵值存在
        $this->assertArrayHasKey('code_verifier', $result);
        $this->assertArrayHasKey('code_challenge', $result);
        $this->assertArrayHasKey('state', $result);

        // 驗證 code_verifier 長度為 43 字符
        $codeVerifier = $result['code_verifier'];
        $this->assertIsString($codeVerifier);
        $this->assertEquals(43, strlen($codeVerifier));

        // 驗證只包含允許的字符 [a-zA-Z0-9\-\._~]
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-\._~]+$/', $codeVerifier);

        // 驗證 code_challenge 不為空
        $this->assertIsString($result['code_challenge']);
        $this->assertNotEmpty($result['code_challenge']);

        // 驗證 state 不為空
        $this->assertIsString($result['state']);
        $this->assertNotEmpty($result['state']);
    }

    /**
     * 測試 generatePKCE 每次產生不同的結果
     */
    public function test_generate_pkce_returns_unique_values(): void
    {
        $result1 = $this->loginService->generatePKCE();
        $result2 = $this->loginService->generatePKCE();

        // 驗證兩次產生的 code_verifier 不同
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertNotEquals($result1['code_verifier'], $result2['code_verifier']);

        // 驗證兩次產生的 code_challenge 不同
        $this->assertNotEquals($result1['code_challenge'], $result2['code_challenge']);

        // 驗證兩次產生的 state 不同
        $this->assertNotEquals($result1['state'], $result2['state']);
    }

    /**
     * 測試 generatePKCE 產生的 code_verifier 字符是否符合規範
     */
    public function test_generate_pkce_uses_valid_characters(): void
    {
        $result = $this->loginService->generatePKCE();
        $this->assertIsArray($result);
        $codeVerifier = $result['code_verifier'];
        $this->assertIsString($codeVerifier);
        $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';

        // 檢查每個字符都在允許的字符集中
        for ($i = 0; $i < strlen($codeVerifier); $i++) {
            $char = $codeVerifier[$i];
            $this->assertStringContainsString($char, $allowedChars, "字符 '{$char}' 不在允許的字符集中");
        }
    }

    /**
     * 測試 setCodeVerifier 成功情況
     */
    public function test_set_code_verifier_success(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';
        $codeChallenge = 'test_code_challenge_123456789abcdefghijklmnop';
        $state = 'test_state_123456789';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge, $state)
            ->willReturn(true);

        $result = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge, $state);

        $this->assertTrue($result);
    }

    /**
     * 測試 setCodeVerifier 失敗情況
     */
    public function test_set_code_verifier_failure(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';
        $codeChallenge = 'test_code_challenge_123456789abcdefghijklmnop';
        $state = 'test_state_123456789';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge, $state)
            ->willReturn(false);

        $result = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge, $state);

        $this->assertFalse($result);
    }

    /**
     * 測試 checkCodeVerifier 存在的情況
     */
    public function test_check_code_verifier_exists(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('checkCodeVerifier')
            ->with($codeVerifier)
            ->willReturn(true);

        $result = $this->loginService->checkCodeVerifier($codeVerifier);

        $this->assertTrue($result);
    }

    /**
     * 測試 checkCodeVerifier 不存在的情況
     */
    public function test_check_code_verifier_not_exists(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('checkCodeVerifier')
            ->with($codeVerifier)
            ->willReturn(false);

        $result = $this->loginService->checkCodeVerifier($codeVerifier);

        $this->assertFalse($result);
    }

    /**
     * 測試 getAndDeleteCodeVerifier 成功取得
     */
    public function test_get_and_delete_code_verifier_success(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';
        $expectedValue = 'retrieved_code_verifier_value';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('getAndDeleteCodeVerifier')
            ->with($codeVerifier)
            ->willReturn($expectedValue);

        $result = $this->loginService->getAndDeleteCodeVerifier($codeVerifier);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * 測試 getAndDeleteCodeVerifier 找不到的情況
     */
    public function test_get_and_delete_code_verifier_not_found(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('getAndDeleteCodeVerifier')
            ->with($codeVerifier)
            ->willReturn(null);

        $result = $this->loginService->getAndDeleteCodeVerifier($codeVerifier);

        $this->assertNull($result);
    }

    /**
     * 測試完整的 code verifier 流程
     */
    public function test_complete_code_verifier_flow(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';
        $codeChallenge = 'test_code_challenge_123456789abcdefghijklmnop';
        $state = 'test_state_123456789';

        // 1. 設定 code verifier
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge, $state)
            ->willReturn(true);

        // 2. 檢查 code verifier 存在
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('checkCodeVerifier')
            ->with($codeVerifier)
            ->willReturn(true);

        // 3. 取得並刪除 code verifier
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('getAndDeleteCodeVerifier')
            ->with($codeVerifier)
            ->willReturn($codeVerifier);

        // 執行流程
        $setResult = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge, $state);
        $checkResult = $this->loginService->checkCodeVerifier($codeVerifier);
        $getResult = $this->loginService->getAndDeleteCodeVerifier($codeVerifier);

        // 驗證結果
        $this->assertTrue($setResult);
        $this->assertTrue($checkResult);
        $this->assertEquals($codeVerifier, $getResult);
    }

    /**
     * 測試根據 state 取得 code verifier 成功情況
     */
    public function test_get_code_verifier_by_state_success(): void
    {
        $state = 'test_state_123456789';
        $expectedCodeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('getCodeVerifierByState')
            ->with($state)
            ->willReturn($expectedCodeVerifier);

        $result = $this->loginService->getCodeVerifierByState($state);

        $this->assertEquals($expectedCodeVerifier, $result);
    }

    /**
     * 測試根據 state 取得 code verifier 找不到的情況
     */
    public function test_get_code_verifier_by_state_not_found(): void
    {
        $state = 'invalid_state';

        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('getCodeVerifierByState')
            ->with($state)
            ->willReturn(null);

        $result = $this->loginService->getCodeVerifierByState($state);

        $this->assertNull($result);
    }

    /**
     * 測試核發 access token 成功情況
     */
    public function test_issue_access_token_by_social_id_success(): void
    {
        $socialId = 'test_social_id_123';
        $socialType = 1;
        $memberId = 1;
        $expectedToken = 'test_access_token_xyz';

        // 建立 mock member
        $mockMember = $this->createMock(Member::class);
        $mockMember->method('__get')
            ->with('id')
            ->willReturn($memberId);

        // 設定 MemberRepo mock 期望
        $this->memberRepoMock
            ->expects($this->once())
            ->method('getMemberBySocialIdAndSocialType')
            ->with($socialId, $socialType)
            ->willReturn($mockMember);

        // 設定 TokenHelper mock 期望
        $this->tokenHelperMock
            ->expects($this->once())
            ->method('issueAccessToken')
            ->with($memberId)
            ->willReturn($expectedToken);

        // 設定 LoginCacheRepo mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setAccessToken')
            ->with($mockMember, $expectedToken);

        $result = $this->loginService->issueAccessTokenBySocialId($socialId, $socialType);

        $this->assertEquals($expectedToken, $result);
    }

    /**
     * 測試核發 access token 會員不存在的情況
     */
    public function test_issue_access_token_by_social_id_member_not_found(): void
    {
        $socialId = 'invalid_social_id';
        $socialType = 1;

        // 設定 MemberRepo mock 期望 - 回傳 null
        $this->memberRepoMock
            ->expects($this->once())
            ->method('getMemberBySocialIdAndSocialType')
            ->with($socialId, $socialType)
            ->willReturn(null);

        // TokenHelper 和 LoginCacheRepo 不應該被呼叫
        $this->tokenHelperMock->expects($this->never())->method('issueAccessToken');
        $this->loginCacheRepoMock->expects($this->never())->method('setAccessToken');

        $result = $this->loginService->issueAccessTokenBySocialId($socialId, $socialType);

        $this->assertEquals('', $result);
    }
}
