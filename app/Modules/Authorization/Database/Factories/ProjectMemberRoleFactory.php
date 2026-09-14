<?php

namespace App\Modules\Authorization\Database\Factories;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\ProjectMemberRole;
use App\Modules\Authorization\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMemberRole>
 */
class ProjectMemberRoleFactory extends Factory
{
    protected $model = ProjectMemberRole::class;

    public function definition(): array
    {
        return [
            'project_member_id' => ProjectMember::factory(),
            'role_id' => function (array $attributes): int {
                $membership = ProjectMember::with('project')->findOrFail($attributes['project_member_id']);

                return Role::factory()
                    ->projectScope()
                    ->create(['organization_id' => $membership->project->organization_id])
                    ->id;
            },
            'assigned_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
