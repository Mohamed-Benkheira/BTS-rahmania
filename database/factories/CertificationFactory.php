<?php

namespace Database\Factories;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        $words = fake()->unique()->words(2);
        $name = is_array($words) ? implode(' ', $words) : $words;

        return [
            'name' => Str::title($name),
            'issuer' => fake()->company(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'validity_period_months' => fake()->randomElement([12, 24, 36, null]),
            'is_active' => true,
        ];
    }
}
