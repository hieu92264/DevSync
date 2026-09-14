<?php

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('DEP-###??')),
            'name' => fake()->randomElement(['Engineering', 'Product', 'Quality Assurance', 'Operations']),
            'organization_id' => Organization::factory(),
            'manager_id' => null,
        ];
    }
}
