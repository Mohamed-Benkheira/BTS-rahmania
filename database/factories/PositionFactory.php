<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        $title = fake()->unique()->jobTitle();

        return [
            'title' => $title,
            'code' => 'POS-'.Str::upper(Str::random(4)),
            'level' => fake()->randomElement(['intern', 'junior', 'mid', 'senior', 'lead', 'manager', 'director']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
