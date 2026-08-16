<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'employee_code' => 'EMP-'.strtoupper((string) Str::random(8)),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'birth_date' => fake()->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'hire_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'employment_status' => fake()->randomElement([EmploymentStatus::Active, EmploymentStatus::Active, EmploymentStatus::Active, EmploymentStatus::OnLeave]),
            'business_unit_id' => null,
            'department_id' => null,
            'team_id' => null,
            'position_id' => null,
            'primary_location_id' => null,
            'manager_id' => null,
            'biography' => fake()->paragraph(),
            'profile_photo_path' => null,
        ];
    }

    public function withUserAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->create()->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'employment_status' => EmploymentStatus::Active,
        ]);
    }
}
