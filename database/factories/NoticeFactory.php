<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement([Notice::STATUS_DISABLE, Notice::STATUS_ENABLE]),
            'created_by' => $this->faker->userName(),
        ];
    }

    /**
     * 啟用的公告
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notice::STATUS_ENABLE,
        ]);
    }

    /**
     * 停用的公告
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notice::STATUS_DISABLE,
        ]);
    }
} 