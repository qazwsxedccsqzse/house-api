<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberFBToken>
 */
class MemberFBTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'token' => fake()->uuid(),
            'type' => fake()->randomElement([1, 2]), // 1: 粉專, 2: 群組
            'expired_at' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * 建立粉專 Token
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 1,
        ]);
    }

    /**
     * 建立群組 Token
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 2,
        ]);
    }
}
