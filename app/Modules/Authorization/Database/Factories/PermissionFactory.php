<?php

namespace App\Modules\Authorization\Database\Factories;

use App\Modules\Authorization\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $resource = fake()->randomElement(['project', 'task', 'incident', 'repository']);
        $action = fake()->randomElement(['read', 'create', 'update', 'delete', 'manage']);
        $suffix = fake()->unique()->numberBetween(100, 999);

        return [
            'is_active' => true,
            'code' => "{$resource}.{$action}.{$suffix}",
            'name' => ucfirst($action).' '.ucfirst($resource)." {$suffix}",
            'remark' => fake()->sentence(),
            'resource' => $resource,
            'action' => $action,
        ];
    }
}
