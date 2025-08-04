<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * 通知工廠
 */
class NotificationFactory extends Factory
{
    /**
     * 模型類別名稱
     */
    protected $model = Notification::class;

    /**
     * 定義模型的預設狀態
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement([1]),
            'user_id' => $this->faker->optional()->numberBetween(1, 10),
            'message' => $this->faker->sentence(),
            'status' => $this->faker->randomElement([1, 2]),
        ];
    }

    /**
     * 未讀狀態
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notification::STATUS_UNREAD,
        ]);
    }

    /**
     * 已讀狀態
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notification::STATUS_READ,
        ]);
    }

    /**
     * 系統通知
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_SYSTEM,
            'user_id' => null,
        ]);
    }

    /**
     * 有用戶的通知
     */
    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $this->faker->numberBetween(1, 10),
        ]);
    }
}
