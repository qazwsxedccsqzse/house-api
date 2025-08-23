<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PlanService;
use App\Repositories\PlanRepo;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Mockery;

/**
 * PlanService 單元測試
 */
class PlanServiceTest extends TestCase
{
    private PlanService $planService;
    private $planRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 建立 PlanRepo 的 Mock
        $this->planRepoMock = Mockery::mock(PlanRepo::class);

        // 建立 PlanService 實例，注入 Mock 的 PlanRepo
        $this->planService = new PlanService($this->planRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * 測試取得方案列表 - 成功案例
     */
    public function test_get_plans_success(): void
    {
        // 準備測試資料
        $plan1 = new Plan();
        $plan1->forceFill([
            'id' => 1,
            'name' => '免費方案',
            'description' => '免費5則 AI 貼文',
            'status' => 1,
            'days' => 30,
            'price' => 0,
        ]);

        $plan2 = new Plan();
        $plan2->forceFill([
            'id' => 2,
            'name' => '月費方案',
            'description' => '30則 AI 貼文',
            'status' => 1,
            'days' => 30,
            'price' => 300,
        ]);

        $mockPlans = new Collection([$plan1, $plan2]);

        // 設定 Mock 行為
        $this->planRepoMock->shouldReceive('getActivePlans')
            ->once()
            ->andReturn($mockPlans);

        // 執行測試
        $result = $this->planService->getPlans();

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // 驗證第一個方案
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('免費方案', $result[0]['name']);
        $this->assertIsArray($result[0]['description']);
        $this->assertContains('免費5則 AI 貼文', $result[0]['description']);
        $this->assertEquals(30, $result[0]['days']);
        $this->assertEquals(0, $result[0]['price']);
        $this->assertNull($result[0]['annual_price']); // 資料庫中沒有這個欄位

        // 驗證第二個方案
        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('月費方案', $result[1]['name']);
        $this->assertIsArray($result[1]['description']);
        $this->assertContains('30則 AI 貼文', $result[1]['description']);
        $this->assertEquals(30, $result[1]['days']);
        $this->assertEquals(300, $result[1]['price']);
        $this->assertNull($result[1]['annual_price']); // 資料庫中沒有這個欄位
    }

    /**
     * 測試取得方案列表 - 空列表案例
     */
    public function test_get_plans_empty_list(): void
    {
        // 準備空的測試資料
        $mockPlans = new Collection([]);

        // 設定 Mock 行為
        $this->planRepoMock->shouldReceive('getActivePlans')
            ->once()
            ->andReturn($mockPlans);

        // 執行測試
        $result = $this->planService->getPlans();

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /**
     * 測試取得方案列表 - 單一方案案例
     */
    public function test_get_plans_single_plan(): void
    {
        // 準備單一方案測試資料
        $plan = new Plan();
        $plan->forceFill([
            'id' => 1,
            'name' => '測試方案',
            'description' => '測試描述',
            'status' => 1,
            'days' => 60,
            'price' => 500,
        ]);

        $mockPlans = new Collection([$plan]);

        // 設定 Mock 行為
        $this->planRepoMock->shouldReceive('getActivePlans')
            ->once()
            ->andReturn($mockPlans);

        // 執行測試
        $result = $this->planService->getPlans();

        // 驗證結果
        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        $planData = $result[0];
        $this->assertEquals(1, $planData['id']);
        $this->assertEquals('測試方案', $planData['name']);
        $this->assertIsArray($planData['description']);
        $this->assertContains('測試描述', $planData['description']);
        $this->assertEquals(60, $planData['days']);
        $this->assertEquals(500, $planData['price']);
        $this->assertNull($planData['annual_price']); // 資料庫中沒有這個欄位
    }

    /**
     * 測試方案資料結構完整性
     */
    public function test_plan_data_structure(): void
    {
        // 準備測試資料
        $plan = new Plan();
        $plan->forceFill([
            'id' => 1,
            'name' => '結構測試方案',
            'description' => '測試資料結構',
            'status' => 1,
            'days' => 90,
            'price' => 1000,
        ]);

        $mockPlans = new Collection([$plan]);

        // 設定 Mock 行為
        $this->planRepoMock->shouldReceive('getActivePlans')
            ->once()
            ->andReturn($mockPlans);

        // 執行測試
        $result = $this->planService->getPlans();
        $planData = $result[0];

        // 驗證資料結構包含所有必要欄位
        $expectedKeys = [
            'id', 'name', 'description', 'days',
            'price', 'annual_price'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $planData, "方案資料缺少 {$key} 欄位");
        }

        // 驗證資料型別
        $this->assertIsInt($planData['id']);
        $this->assertIsString($planData['name']);
        $this->assertIsArray($planData['description']);
        $this->assertIsInt($planData['days']);
        $this->assertIsInt($planData['price']);
        $this->assertNull($planData['annual_price']); // 資料庫中沒有這個欄位
    }
}
