<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * 執行資料庫填充
     */
    public function run(): void
    {
        // 取得現有的會員和方案
        $members = Member::all();
        $plans = Plan::all();

        if ($members->isEmpty() || $plans->isEmpty()) {
            $this->command->warn('請先執行 MemberSeeder 和 PlanSeeder');
            return;
        }

        // 為每個會員建立一些訂單
        foreach ($members as $member) {
            // 隨機建立 1-5 個訂單
            $orderCount = rand(1, 5);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $plan = $plans->random();
                $startDate = now()->subDays(rand(0, 365));
                $endDate = $startDate->copy()->addDays(rand(30, 365));
                
                Order::create([
                    'member_id' => $member->id,
                    'plan_id' => $plan->id,
                    'status' => $this->getRandomStatus($startDate, $endDate),
                    'price' => $plan->price ?? rand(100, 5000),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
            }
        }

        $this->command->info('OrderSeeder 執行完成');
    }

    /**
     * 根據日期取得隨機狀態
     */
    private function getRandomStatus($startDate, $endDate): string
    {
        $now = now();
        
        if ($now < $startDate) {
            return 'pending';
        } elseif ($now >= $startDate && $now <= $endDate) {
            return 'active';
        } else {
            return 'completed';
        }
    }
}
