<?php

namespace Database\Seeders\Identity;

use App\Domain\Identity\Enums\RoleEnum;
use App\Domain\Identity\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultRole = RoleEnum::STUDENT;

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::updateOrCreate(
                [
                    'title' => $roleEnum->value
                ],
                [
                    'label' => $roleEnum->label(),
                    'is_default' => $roleEnum === $defaultRole,
                ]
            );
        }
    }
}
