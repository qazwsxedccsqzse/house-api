<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RolePermission>
 */
class RolePermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RolePermission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::factory(),
            'permission_id' => Permission::factory(),
        ];
    }

    /**
     * 為指定角色創建角色權限
     */
    public function forRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $role->id,
        ]);
    }

    /**
     * 為指定權限創建角色權限
     */
    public function forPermission(Permission $permission): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_id' => $permission->id,
        ]);
    }
}
