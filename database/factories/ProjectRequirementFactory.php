<?php

namespace Database\Factories;

use App\Enums\RequirementType;
use App\Models\Project;
use App\Models\ProjectRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectRequirement>
 */
class ProjectRequirementFactory extends Factory
{
    protected $model = ProjectRequirement::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->words(4, true),
            'description' => fake()->sentence(),
            'requirement_type' => fake()->randomElement(RequirementType::cases()),
            'is_mandatory' => fake()->boolean(70),
            'weight' => fake()->randomFloat(2, 0, 5),
        ];
    }
}
