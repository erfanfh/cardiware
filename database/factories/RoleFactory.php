<?php

namespace Database\Factories;

use App\Domain\Identity\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
            'is_default' => false,
            'is_super_admin' => false,
        ];
    }

    /**
     * Mark this role as the application's default role.
     */
    public function default(): static
    {
        return $this->state(fn(): array => [
            'is_default' => true,
        ]);
    }
}
