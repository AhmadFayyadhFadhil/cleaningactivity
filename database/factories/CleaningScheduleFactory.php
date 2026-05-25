<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\CleaningSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CleaningSchedule>
 */
class CleaningScheduleFactory extends Factory
{
    protected $model = CleaningSchedule::class;

    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'schedule_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'schedule_time' => $this->faker->time('H:i'),
            'assigned_to_id' => User::factory(),
            'supervisor_id' => User::factory(),
            'status' => 'scheduled',
            'priority' => 'medium',
            'notes' => $this->faker->sentence(),
        ];
    }
}
