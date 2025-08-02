<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立超級管理員
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '超級管理員',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'status' => 1,
            ]
        );

        // 為超級管理員分配超級管理員角色
        $superAdminRole = Role::where('code', 'super-admin')->first();
        if ($superAdminRole) {
            $admin->roles()->sync([$superAdminRole->id]);
        }
    }
}
