<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeAvailability>
 */
class EmployeeAvailabilityFactory extends Factory
{
    protected $model = EmployeeAvailability::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'employee_id' => Employee::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+30 days', '+180 days')->format('Y-m-d'),
            'availability_percentage' => fake()->randomElement([100, 100, 75, 50, 25]),
            'status' => fake()->randomElement(AvailabilityStatus::cases()),
            'reason' => fake()->randomElement(['Project release', 'Planned leave', 'Training', 'Part-time schedule', null]),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AvailabilityStatus::Available,
            'availability_percentage' => 100,
        ]);
    }
}
