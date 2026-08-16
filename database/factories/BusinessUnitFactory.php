<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessUnit>
 */
class BusinessUnitFactory extends Factory
{
    protected $model = BusinessUnit::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'code' => 'BU-'.Str::upper(Str::random(4)),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
