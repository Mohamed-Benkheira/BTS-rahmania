<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Admin\Resources\Assignments\RelationManagers\MembersRelationManager;
use App\Filament\Admin\Resources\Assignments\RelationManagers\StatusHistoryRelationManager;
use App\Filament\Admin\Resources\Assignments\RelationManagers\TeamsRelationManager;
use App\Filament\Admin\Resources\Employees\Pages\EditEmployee;
use App\Filament\Admin\Resources\Employees\RelationManagers\AvailabilitiesRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\CertificationsRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\LanguagesRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\SkillsRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\WorkloadsRelationManager;
use App\Filament\Admin\Resources\Projects\Pages\EditProject;
use App\Filament\Admin\Resources\Projects\RelationManagers\RecommendationsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredCertificationsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredLanguagesRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredSkillsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequirementsRelationManager;
use App\Filament\Admin\Widgets\AllocationVsAvailabilityChart;
use App\Filament\Admin\Widgets\EmployeesByDepartmentChart;
use App\Filament\Admin\Widgets\ExpiringCertificationsTable;
use App\Filament\Admin\Widgets\OverAllocatedEmployeesTable;
use App\Filament\Admin\Widgets\ProjectsByStatusChart;
use App\Filament\Admin\Widgets\ProjectsNeedingStaffingTable;
use App\Filament\Admin\Widgets\RecentActivityTable;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Models\Assignment;
use App\Models\BusinessUnit;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Language;
use App\Models\Location;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectEvaluation;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'admin@djezzy.test')->firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_every_admin_resource_page_renders(): void
    {
        $failures = [];

        foreach ($this->resources() as $slug => $model) {
            $base = "/admin/{$slug}";

            foreach ([$base, "{$base}/create"] as $url) {
                $failures = $this->assertRenders($url, $slug, $failures);
            }

            if ($model !== null) {
                $record = $model::query()->first();

                if ($record !== null) {
                    $url = "{$base}/{$record->getRouteKey()}/edit";
                    $failures = $this->assertRenders($url, "{$slug} edit", $failures);
                }
            }
        }

        $this->assertSame([], $failures, "The following admin pages failed to render:\n".implode("\n", $failures));
    }

    public function test_admin_dashboard_renders(): void
    {
        $this->get('/admin')->assertOk();
    }

    public function test_every_dashboard_widget_renders_content(): void
    {
        $widgets = [
            StatsOverview::class => ['Active Employees', 'Active Projects', 'Active Assignments', 'Certifications Expiring Soon', 'fi-tooltip'],
            EmployeesByDepartmentChart::class => ['Employees by Department'],
            ProjectsByStatusChart::class => ['Projects by Status'],
            AllocationVsAvailabilityChart::class => ['Workload vs. Availability'],
            ExpiringCertificationsTable::class => ['Certifications Expiring Soon', 'Days Left'],
            OverAllocatedEmployeesTable::class => ['Over-Allocated Employees'],
            ProjectsNeedingStaffingTable::class => ['Projects Needing Staffing'],
            RecentActivityTable::class => ['Recent Activity'],
        ];

        foreach ($widgets as $widget => $markers) {
            $html = Livewire::test($widget)->assertOk()->html();

            foreach ($markers as $marker) {
                $this->assertStringContainsString($marker, $html, "[{$widget}] is missing [{$marker}].");
            }
        }
    }

    public function test_every_admin_relation_manager_renders(): void
    {
        $failures = [];

        foreach ($this->relationManagers() as $label => [$relationManager, $ownerModel, $pageClass]) {
            $record = $ownerModel::query()->first();

            if ($record === null) {
                continue;
            }

            try {
                Livewire::test($relationManager, [
                    'ownerRecord' => $record,
                    'pageClass' => $pageClass,
                ])->assertOk();
            } catch (AssertionFailedError $e) {
                $failures[] = "[{$label}] {$relationManager}\n{$e->getMessage()}";
            }
        }

        $this->assertSame([], $failures, "The following relation managers failed to render:\n".implode("\n", $failures));
    }

    /**
     * @return array<string, array{class-string, class-string<Model>, class-string}>
     */
    private function relationManagers(): array
    {
        return [
            'assignment members' => [
                MembersRelationManager::class,
                Assignment::class,
                EditAssignment::class,
            ],
            'assignment teams' => [
                TeamsRelationManager::class,
                Assignment::class,
                EditAssignment::class,
            ],
            'assignment status history' => [
                StatusHistoryRelationManager::class,
                Assignment::class,
                EditAssignment::class,
            ],
            'employee skills' => [
                SkillsRelationManager::class,
                Employee::class,
                EditEmployee::class,
            ],
            'employee certifications' => [
                CertificationsRelationManager::class,
                Employee::class,
                EditEmployee::class,
            ],
            'employee languages' => [
                LanguagesRelationManager::class,
                Employee::class,
                EditEmployee::class,
            ],
            'employee availabilities' => [
                AvailabilitiesRelationManager::class,
                Employee::class,
                EditEmployee::class,
            ],
            'employee workloads' => [
                WorkloadsRelationManager::class,
                Employee::class,
                EditEmployee::class,
            ],
            'project requirements' => [
                RequirementsRelationManager::class,
                Project::class,
                EditProject::class,
            ],
            'project required skills' => [
                RequiredSkillsRelationManager::class,
                Project::class,
                EditProject::class,
            ],
            'project required certifications' => [
                RequiredCertificationsRelationManager::class,
                Project::class,
                EditProject::class,
            ],
            'project required languages' => [
                RequiredLanguagesRelationManager::class,
                Project::class,
                EditProject::class,
            ],
            'project recommendations' => [
                RecommendationsRelationManager::class,
                Project::class,
                EditProject::class,
            ],
        ];
    }

    /**
     * @return array<string, class-string<Model>|null>
     */
    private function resources(): array
    {
        return [
            'assignments' => Assignment::class,
            'business-units' => BusinessUnit::class,
            'certifications' => Certification::class,
            'departments' => Department::class,
            'employees' => Employee::class,
            'languages' => Language::class,
            'locations' => Location::class,
            'positions' => Position::class,
            'project-categories' => ProjectCategory::class,
            'projects' => Project::class,
            'project-evaluations' => ProjectEvaluation::class,
            'skill-categories' => SkillCategory::class,
            'skills' => Skill::class,
            'teams' => Team::class,
            'users' => User::class,
            'roles' => Role::class,
        ];
    }

    /**
     * @param  list<string>  $failures
     * @return list<string>
     */
    private function assertRenders(string $url, string $label, array $failures): array
    {
        try {
            $response = $this->get($url);
            $response->assertOk();
        } catch (AssertionFailedError $e) {
            $failures[] = "[{$label}] {$url}\n{$e->getMessage()}";
        }

        return $failures;
    }
}
