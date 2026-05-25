<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'area_code' => 'AREA-' . $this->faker->unique()->numerify('####'),
            'area_name' => $this->faker->word() . ' Room',
            'location' => 'Building ' . $this->faker->randomElement(['A', 'B', 'C', 'D']) . ', Floor ' . $this->faker->numberBetween(1, 5),
            'floor' => $this->faker->numberBetween(1, 5),
            'building' => 'Building ' . $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'pic_user_id' => User::factory(),
            'status' => 'active',
            'schedule_frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
        ];
    }
}
