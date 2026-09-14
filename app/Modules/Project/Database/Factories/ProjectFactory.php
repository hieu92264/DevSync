<?php

namespace App\Modules\Project\Database\Factories;

use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'code' => Str::lower(fake()->unique()->bothify('project-###??')),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['planning', 'active', 'on_hold', 'completed']),
            'repository_url' => 'https://github.com/devsync/'.fake()->slug(3),
            'organization_id' => Organization::factory(),
            'lead_id' => User::factory(),
        ];
    }
}
