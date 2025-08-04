<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'status' => 1,
            'days' => fake()->numberBetween(7, 365),
            'price' => fake()->numberBetween(100, 10000),
        ];
    }

    /**
     * 建立停用的方案
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * 建立短期方案
     */
    public function shortTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'days' => fake()->numberBetween(7, 30),
            'price' => fake()->numberBetween(100, 1000),
        ]);
    }

    /**
     * 建立長期方案
     */
    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'days' => fake()->numberBetween(180, 365),
            'price' => fake()->numberBetween(5000, 10000),
        ]);
    }
}
