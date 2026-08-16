<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'address' => fake()->streetAddress(),
            'timezone' => fake()->timezone(),
            'is_active' => true,
        ];
    }
}
