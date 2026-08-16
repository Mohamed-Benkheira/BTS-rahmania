<?php

namespace Database\Factories;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'assigned_by' => User::factory(),
            'approved_by' => null,
            'assignment_type' => fake()->randomElement([AssignmentType::Employee, AssignmentType::Team, AssignmentType::Department]),
            'status' => fake()->randomElement([
                AssignmentStatus::Pending,
                AssignmentStatus::Approved,
                AssignmentStatus::Active,
            ]),
            'assigned_at' => now(),
            'approved_at' => null,
            'start_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+60 days', '+365 days')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Active,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}
