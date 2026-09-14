<?php

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'is_active' => true,
            'code' => Str::lower(fake()->unique()->bothify('org-###??')),
            'name' => fake()->company(),
        ];
    }
}
