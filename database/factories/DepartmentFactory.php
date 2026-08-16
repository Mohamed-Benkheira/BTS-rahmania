<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'business_unit_id' => null,
            'parent_department_id' => null,
            'name' => $name,
            'code' => 'DEPT-'.Str::upper(Str::random(4)),
            'description' => fake()->sentence(),
            'manager_id' => null,
            'is_active' => true,
        ];
    }
}
