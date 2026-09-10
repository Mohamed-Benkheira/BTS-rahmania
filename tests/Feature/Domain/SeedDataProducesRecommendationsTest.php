<?php

namespace Tests\Feature\Domain;

use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Services\RecommendationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedDataProducesRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_project_yields_recommendations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@djezzy.test')->firstOrFail();
        $projects = Project::all();

        $this->assertNotCount(0, $projects);
        $this->assertGreaterThan(0, Employee::where('employment_status', 'active')->count());

        foreach ($projects as $project) {
            $run = app(RecommendationService::class)->run($project, $admin);

            $recs = $run->recommendations()->orderBy('rank')->get();

            $this->assertGreaterThan(
                0,
                $recs->count(),
                "[{$project->name}] ({$project->assignment_mode->value}) produced no recommendations",
            );

            $this->assertNull(
                $run->blockers,
                "[{$project->name}] unexpectedly recorded mandatory blockers: ".implode('; ', $run->blockers ?? []),
            );
        }
    }
}
