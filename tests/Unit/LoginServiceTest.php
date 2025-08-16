<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LoginService;
use App\Repositories\LoginCacheRepo;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LoginServiceTest extends TestCase
{
    private LoginService $loginService;
    private MockObject|LoginCacheRepo $loginCacheRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 建立 LoginCacheRepo mock
        $this->loginCacheRepoMock = $this->createMock(LoginCacheRepo::class);
        
        // 建立 LoginService 實例
        $this->loginService = new LoginService($this->loginCacheRepoMock);
    }

    /**
     * 測試 generateCodeVerifier 方法
     */
    public function test_generate_code_verifier_returns_valid_string(): void
    {
        $codeVerifier = $this->loginService->generateCodeVerifier();
        
        // 驗證長度為 43 字符
        $this->assertEquals(43, strlen($codeVerifier));
        
        // 驗證只包含允許的字符 [a-zA-Z0-9\-\._~]
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-\._~]+$/', $codeVerifier);
    }

    /**
     * 測試 generateCodeVerifier 每次產生不同的結果
     */
    public function test_generate_code_verifier_returns_unique_values(): void
    {
        $codeVerifier1 = $this->loginService->generateCodeVerifier();
        $codeVerifier2 = $this->loginService->generateCodeVerifier();
        
        // 驗證兩次產生的值不同
        $this->assertNotEquals($codeVerifier1, $codeVerifier2);
    }

    /**
     * 測試 generateCodeVerifier 產生的字符是否符合規範
     */
    public function test_generate_code_verifier_uses_valid_characters(): void
    {
        $codeVerifier = $this->loginService->generateCodeVerifier();
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
        
        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge)
            ->willReturn(true);
        
        $result = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge);
        
        $this->assertTrue($result);
    }

    /**
     * 測試 setCodeVerifier 失敗情況
     */
    public function test_set_code_verifier_failure(): void
    {
        $codeVerifier = 'test_code_verifier_123456789abcdefghijklmnop';
        $codeChallenge = 'test_code_challenge_123456789abcdefghijklmnop';
        
        // 設定 mock 期望
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge)
            ->willReturn(false);
        
        $result = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge);
        
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
        
        // 1. 設定 code verifier
        $this->loginCacheRepoMock
            ->expects($this->once())
            ->method('setCodeVerifier')
            ->with($codeVerifier, $codeChallenge)
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
        $setResult = $this->loginService->setCodeVerifier($codeVerifier, $codeChallenge);
        $checkResult = $this->loginService->checkCodeVerifier($codeVerifier);
        $getResult = $this->loginService->getAndDeleteCodeVerifier($codeVerifier);
        
        // 驗證結果
        $this->assertTrue($setResult);
        $this->assertTrue($checkResult);
        $this->assertEquals($codeVerifier, $getResult);
    }
}