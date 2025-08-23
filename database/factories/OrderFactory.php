<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * 定義模型的預設狀態
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'member_id' => Member::factory(),
            'plan_id' => Plan::factory(),
            'status' => $this->faker->randomElement(['pending', 'active', 'completed', 'cancelled']),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * 待處理狀態的訂單
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * 啟用狀態的訂單
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * 已完成狀態的訂單
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * 已取消狀態的訂單
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
