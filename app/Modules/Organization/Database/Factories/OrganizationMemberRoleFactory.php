<?php

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Authorization\Models\Role;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Organization\Models\OrganizationMemberRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationMemberRole>
 */
class OrganizationMemberRoleFactory extends Factory
{
    protected $model = OrganizationMemberRole::class;

    public function definition(): array
    {
        return [
            'organization_member_id' => OrganizationMember::factory(),
            'role_id' => function (array $attributes): int {
                $membership = OrganizationMember::findOrFail($attributes['organization_member_id']);

                return Role::factory()
                    ->organizationScope()
                    ->create(['organization_id' => $membership->organization_id])
                    ->id;
            },
            'assigned_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
