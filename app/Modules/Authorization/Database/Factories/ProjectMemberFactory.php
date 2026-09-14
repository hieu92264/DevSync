<?php

namespace App\Modules\Authorization\Database\Factories;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Identity\Models\User;
use App\Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMember>
 */
class ProjectMemberFactory extends Factory
{
    protected $model = ProjectMember::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'team_type' => fake()->randomElement(['backend', 'frontend', 'quality', 'product']),
            'joined_at' => fake()->dateTimeBetween('-12 months', '-1 week'),
            'left_at' => null,
            'is_active' => true,
        ];
    }
}
