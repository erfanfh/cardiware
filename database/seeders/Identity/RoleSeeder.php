<?php

namespace Database\Seeders\Identity;

use App\Domain\Identity\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'title' => 'ادمین کل',
                'is_super_admin' => true,
            ],
            [
                'title' => 'کاربر عادی',
                'is_default' => true
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['title' => $role['title']],
                [
                    'is_default' => $role['is_default'] ?? false,
                    'is_super_admin' => $role['is_super_admin'] ?? false,
                ]
            );
        }
    }
}
