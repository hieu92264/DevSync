<?php

namespace App\Modules\Authorization\Database\Factories;

use App\Modules\Authorization\Models\Role;
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
            'code' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'name' => $name,
            'priority' => fake()->numberBetween(10, 100),
            'remark' => fake()->sentence(),
        ];
    }
}
