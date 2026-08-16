<?php

namespace Database\Factories;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectCategory>
 */
class ProjectCategoryFactory extends Factory
{
    protected $model = ProjectCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
