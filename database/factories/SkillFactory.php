<?php

namespace Database\Factories;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'skill_category_id' => SkillCategory::factory(),
            'name' => $name,
            'slug' => $name.'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
