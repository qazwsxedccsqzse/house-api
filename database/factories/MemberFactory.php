<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'estate_broker_number' => fake()->regexify('[A-Z]{2}[0-9]{6}'),
            'status' => 1,
            'line_id' => fake()->optional()->userName(),
            'line_picture' => fake()->optional()->imageUrl(),
            'plan_id' => null,
            'plan_start_date' => null,
            'plan_end_date' => null,
        ];
    }
}
