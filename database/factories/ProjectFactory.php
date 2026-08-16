<?php

namespace Database\Factories;

use App\Enums\AssignmentMode;
use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $words = fake()->unique()->words(3);
        $name = is_array($words) ? implode(' ', $words) : $words;

        return [
            'project_code' => 'PRJ-'.strtoupper((string) Str::random(6)),
            'name' => Str::title($name),
            'description' => fake()->paragraph(),
            'category_id' => ProjectCategory::factory(),
            'requesting_department_id' => null,
            'owning_department_id' => null,
            'project_manager_id' => null,
            'created_by' => User::factory(),
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'status' => fake()->randomElement([
                ProjectStatus::Draft,
                ProjectStatus::Submitted,
                ProjectStatus::UnderReview,
                ProjectStatus::Staffing,
                ProjectStatus::InProgress,
            ]),
            'confidentiality_level' => fake()->randomElement(ConfidentialityLevel::cases()),
            'start_date' => fake()->dateTimeBetween('-60 days', '+30 days')->format('Y-m-d'),
            'target_end_date' => fake()->dateTimeBetween('+60 days', '+365 days')->format('Y-m-d'),
            'estimated_hours' => fake()->numberBetween(100, 5000),
            'estimated_budget' => fake()->numberBetween(10000, 2000000),
            'required_members_count' => fake()->numberBetween(1, 12),
            'assignment_mode' => fake()->randomElement(AssignmentMode::cases()),
        ];
    }
}
