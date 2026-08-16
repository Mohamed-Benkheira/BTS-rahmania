<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectEvaluation>
 */
class ProjectEvaluationFactory extends Factory
{
    protected $model = ProjectEvaluation::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'employee_id' => null,
            'evaluator_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'communication_rating' => fake()->numberBetween(1, 5),
            'delivery_rating' => fake()->numberBetween(1, 5),
            'quality_rating' => fake()->numberBetween(1, 5),
            'comments' => fake()->optional()->paragraph(),
            'evaluated_at' => now(),
        ];
    }
}
