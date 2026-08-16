<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeWorkload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeWorkload>
 */
class EmployeeWorkloadFactory extends Factory
{
    protected $model = EmployeeWorkload::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'employee_id' => Employee::factory(),
            'period_start' => $start->format('Y-m-d'),
            'period_end' => fake()->dateTimeBetween('+30 days', '+180 days')->format('Y-m-d'),
            'allocated_percentage' => fake()->randomElement([0, 25, 50, 75, 100]),
            'allocated_hours' => fake()->randomElement([40, 80, 120, 160]),
            'source' => fake()->randomElement(['system', 'manual', 'import']),
        ];
    }
}
