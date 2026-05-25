<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    protected $model = ChecklistItem::class;

    public function definition(): array
    {
        return [
            'item_code' => 'CHK-' . $this->faker->unique()->numerify('####'),
            'item_name' => $this->faker->word() . ' Check',
            'category' => $this->faker->randomElement(['Lantai', 'Dinding', 'Furniture', 'Kaca', 'Toilet']),
            'description' => $this->faker->sentence(),
            'instruction' => '1. Prepare tools\\n2. Perform check\\n3. Report status',
            'status' => 'active',
        ];
    }
}
