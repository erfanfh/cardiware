<?php

namespace Database\Seeders;

use Database\Seeders\Feature\FeatureDefinitionSeeder;
use Database\Seeders\Identity\PermissionSeeder;
use Database\Seeders\Identity\RoleSeeder;
use Database\Seeders\Layout\PageLayoutSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            PageLayoutSeeder::class,
            FeatureDefinitionSeeder::class
        ]);
    }
}
