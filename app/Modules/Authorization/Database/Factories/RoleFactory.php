<?php

namespace App\Modules\Authorization\Database\Factories;

use App\Modules\Authorization\Models\Role;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Project Manager', 'Developer', 'Quality Engineer', 'Product Owner']);

        return [
            'organization_id' => Organization::factory(),
            'scope' => fake()->randomElement([Role::SCOPE_ORGANIZATION, Role::SCOPE_PROJECT]),
            'is_active' => true,
            'code' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'name' => $name,
            'priority' => fake()->numberBetween(10, 100),
            'remark' => fake()->sentence(),
        ];
    }

    public function organizationScope(): static
    {
        return $this->state(fn (array $attributes) => ['scope' => Role::SCOPE_ORGANIZATION]);
    }

    public function projectScope(): static
    {
        return $this->state(fn (array $attributes) => ['scope' => Role::SCOPE_PROJECT]);
    }
}
