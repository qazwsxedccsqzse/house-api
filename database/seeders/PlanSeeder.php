<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => 1,
                'name' => '免費方案',
                'description' => "免費5則 AI 貼文\n只能發在1個粉專+1個社團\n無法使用排程發文\n圖片大小限制在2MB以下",
                'status' => 1,
                'days' => 30,
                'price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '月費方案',
                'description' => "30則 AI 貼文\n無限制粉專與社團數量\n可用排程發文\n圖片大小提升到5MB",
                'status' => 1,
                'days' => 30,
                'price' => 300,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '年費方案',
                'description' => "30則 AI 貼文\n無限制粉專與社團數量\n可用排程發文\n圖片大小提升到5MB",
                'status' => 1,
                'days' => 365,
                'price' => 3000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Plan::insert($plans);
    }
}
