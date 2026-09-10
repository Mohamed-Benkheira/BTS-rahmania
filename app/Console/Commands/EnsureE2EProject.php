<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Console\Command;

class EnsureE2EProject extends Command
{
    protected $signature = 'app:ensure-e2e-project';

    protected $description = 'Create (or reuse) an e2e project with no current approved/active assignment and print its id';

    public function handle(): int
    {
        $admin = User::query()
            ->where('email', 'admin@djezzy.test')
            ->first();

        $project = Project::query()
            ->where('name', 'like', 'E2E Playwright Project%')
            ->whereDoesntHave('assignments', function ($q) {
                $q->whereIn('status', ['approved', 'active']);
            })
            ->orderBy('id')
            ->first();

        if ($project !== null) {
            $this->line((string) $project->id);

            return self::SUCCESS;
        }

        $project = Project::query()->create([
            'name' => 'E2E Playwright Project '.now()->format('YmdHis'),
            'project_code' => 'E2E-'.now()->format('YmdHis'),
            'description' => 'Automated browser QA fixture.',
            'category_id' => ProjectCategory::query()->value('id'),
            'requesting_department_id' => null,
            'owning_department_id' => null,
            'project_manager_id' => null,
            'created_by' => $admin?->id ?? User::factory()->create()->id,
            'priority' => 'medium',
            'status' => 'staffing',
            'confidentiality_level' => 'internal',
            'start_date' => now()->addDay()->toDateString(),
            'target_end_date' => now()->addMonths(3)->toDateString(),
            'estimated_hours' => 800,
            'estimated_budget' => 250000,
            'required_members_count' => 3,
            'assignment_mode' => 'single_employee',
        ]);

        $this->line((string) $project->id);

        return self::SUCCESS;
    }
}
