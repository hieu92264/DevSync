<?php

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Organization\Models\Equipment;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['laptop', 'monitor', 'mobile-device']);

        return [
            'code' => Str::upper(fake()->unique()->bothify('EQ-####??')),
            'name' => match ($type) {
                'laptop' => fake()->randomElement(['MacBook Pro 14', 'ThinkPad X1 Carbon', 'Dell XPS 15']),
                'monitor' => fake()->randomElement(['Dell UltraSharp 27', 'LG UltraFine 27']),
                default => fake()->randomElement(['iPhone 15', 'Samsung Galaxy S24']),
            },
            'type' => $type,
            'serial_number' => Str::upper(fake()->unique()->bothify('SN-########')),
            'status' => fake()->randomElement(['available', 'assigned', 'maintenance']),
            'specification' => fake()->randomElement(['16GB RAM / 512GB SSD', '32GB RAM / 1TB SSD', '4K IPS panel']),
            'purchase_at' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'organization_id' => Organization::factory(),
        ];
    }
}
