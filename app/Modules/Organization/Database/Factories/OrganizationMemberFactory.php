<?php

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationMember>
 */
class OrganizationMemberFactory extends Factory
{
    protected $model = OrganizationMember::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'joined_at' => fake()->dateTimeBetween('-18 months', '-1 month'),
            'left_at' => null,
            'is_active' => true,
        ];
    }
}
