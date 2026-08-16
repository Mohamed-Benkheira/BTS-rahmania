<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'department_id' => null,
            'name' => Str::title($name).' Team',
            'code' => 'TEAM-'.Str::upper(Str::random(4)),
            'description' => fake()->sentence(),
            'team_leader_id' => null,
            'is_active' => true,
        ];
    }
}
