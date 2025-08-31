<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FbToken;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FbToken>
 */
class FbTokenFactory extends Factory
{
    /**
     * 定義模型的預設狀態
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'type' => $this->faker->randomElement([FbToken::TYPE_PAGE, FbToken::TYPE_GROUP]),
            'fb_id' => $this->faker->numerify('##########'),
            'name' => $this->faker->company(),
            'access_token' => $this->faker->sha1(),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * 粉絲頁狀態
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FbToken::TYPE_PAGE,
        ]);
    }

    /**
     * 群組狀態
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FbToken::TYPE_GROUP,
        ]);
    }

    /**
     * 已過期狀態
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * 永不過期狀態
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
