<?php

namespace Tests\Feature\Domain;

use App\Enums\EmploymentStatus;
use App\Models\AuditLog;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\EmployeeWorkload;
use App\Models\Project;
use App\Models\Recommendation;
use App\Models\RecommendationRun;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function activeEmployee(): Employee
    {
        return Employee::factory()->active()->create();
    }

    private function project(): Project
    {
        return Project::factory()->create(['created_by' => $this->admin()->id, 'assignment_mode' => 'single_employee']);
    }

    public function test_run_creates_recommendation_run_and_rankings(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $e1 = $this->activeEmployee();
        $e2 = $this->activeEmployee();

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertInstanceOf(RecommendationRun::class, $run);
        $this->assertDatabaseHas('recommendation_runs', [
            'project_id' => $project->id,
            'executed_by' => $admin->id,
        ]);

        $recommendations = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(2, $recommendations);
        $this->assertTrue($recommendations->contains('employee_id', $e1->id));
        $this->assertTrue($recommendations->contains('employee_id', $e2->id));
    }

    public function test_eligible_employees_excludes_inactive(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $active = $this->activeEmployee();
        Employee::factory()->create(['employment_status' => EmploymentStatus::Resigned->value]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($active->id, $recs->first()->employee_id);
    }

    public function test_eligible_employees_excludes_already_assigned(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $e1 = $this->activeEmployee();
        $e2 = $this->activeEmployee();

        $assignment = $project->assignments()->create([
            'assigned_by' => $admin->id,
            'assignment_type' => 'employee',
            'status' => 'active',
        ]);
        $assignment->members()->attach($e1->id);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($e2->id, $recs->first()->employee_id);
    }

    public function test_scoring_favors_employee_with_matching_skills(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $skill = Skill::factory()->create();
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 3,
            'is_mandatory' => false,
            'weight' => 5.0,
        ]);

        $skilled = $this->activeEmployee();
        $skilled->skills()->attach($skill->id, ['proficiency_level' => 5]);

        $unskilled = $this->activeEmployee();

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertCount(2, $recs);
        $this->assertEquals($skilled->id, $recs->first()->employee_id);
        $this->assertGreaterThan($recs->last()->total_score, $recs->first()->total_score);
    }

    public function test_scoring_favors_employee_with_higher_availability(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $available = $this->activeEmployee();
        EmployeeAvailability::create([
            'employee_id' => $available->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'availability_percentage' => 100,
            'status' => 'available',
        ]);

        $limited = $this->activeEmployee();
        EmployeeAvailability::create([
            'employee_id' => $limited->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'availability_percentage' => 30,
            'status' => 'partially_available',
        ]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertCount(2, $recs);
        $this->assertEquals($available->id, $recs->first()->employee_id);
    }

    public function test_scoring_favors_employee_with_lower_workload(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $free = $this->activeEmployee();
        $busy = $this->activeEmployee();

        EmployeeWorkload::create([
            'employee_id' => $busy->id,
            'period_start' => now()->subMonth(),
            'period_end' => now()->addMonth(),
            'allocated_percentage' => 90,
            'source' => 'system',
        ]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertEquals($free->id, $recs->first()->employee_id);
    }

    public function test_run_stores_criteria_snapshot(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertIsArray($run->criteria_snapshot);
        $this->assertEquals($project->id, $run->criteria_snapshot['project_id']);
        $this->assertArrayHasKey('weights', $run->criteria_snapshot);
    }

    public function test_run_records_audit_log(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'recommendation.run',
            'auditable_type' => RecommendationRun::class,
            'auditable_id' => $run->id,
        ]);
    }

    public function test_run_returns_loaded_relationships(): void
    {
        $project = $this->project();
        $admin = $this->admin();
        $this->activeEmployee();

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertNotNull($run->relationLoaded('recommendations'));
        $this->assertNotNull($run->recommendations->first()->relationLoaded('employee'));
    }

    public function test_team_mode_recommends_teams(): void
    {
        $admin = $this->admin();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'team',
        ]);

        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $team1->employees()->save($this->activeEmployee());
        $team2->employees()->save($this->activeEmployee());

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(2, $recs);
        $this->assertTrue($recs->contains('team_id', $team1->id));
        $this->assertTrue($recs->contains('team_id', $team2->id));
        $this->assertNull($recs->first()->employee_id);
    }

    public function test_team_mode_excludes_already_assigned_teams(): void
    {
        $admin = $this->admin();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'team',
        ]);

        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        $team1->employees()->save($this->activeEmployee());
        $team2->employees()->save($this->activeEmployee());

        $assignment = $project->assignments()->create([
            'assigned_by' => $admin->id,
            'assignment_type' => 'team',
            'status' => 'active',
        ]);
        $assignment->teams()->attach($team1->id);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($team2->id, $recs->first()->team_id);
    }

    public function test_team_mode_scores_collective_skills(): void
    {
        $admin = $this->admin();
        $skill = Skill::factory()->create();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'team',
        ]);
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 3,
            'is_mandatory' => false,
            'weight' => 5.0,
        ]);

        $skilledTeam = Team::factory()->create();
        $skilledEmployee = $this->activeEmployee();
        $skilledEmployee->skills()->attach($skill->id, ['proficiency_level' => 5]);
        $skilledTeam->employees()->save($skilledEmployee);

        $unskilledTeam = Team::factory()->create();
        $unskilledTeam->employees()->save($this->activeEmployee());

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertEquals($skilledTeam->id, $recs->first()->team_id);
        $this->assertGreaterThan($recs->last()->total_score, $recs->first()->total_score);
    }

    public function test_department_mode_recommends_departments(): void
    {
        $admin = $this->admin();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'department',
        ]);

        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();

        $dept1->employees()->save($this->activeEmployee());
        $dept2->employees()->save($this->activeEmployee());

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(2, $recs);
        $this->assertTrue($recs->contains('department_id', $dept1->id));
        $this->assertTrue($recs->contains('department_id', $dept2->id));
        $this->assertNull($recs->first()->employee_id);
        $this->assertNull($recs->first()->team_id);
    }

    public function test_department_mode_excludes_empty_departments(): void
    {
        $admin = $this->admin();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'department',
        ]);

        $deptWithEmployee = Department::factory()->create();
        $deptWithEmployee->employees()->save($this->activeEmployee());

        Department::factory()->create(); // empty department

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($deptWithEmployee->id, $recs->first()->department_id);
    }

    public function test_department_mode_scores_collective_skills(): void
    {
        $admin = $this->admin();
        $skill = Skill::factory()->create();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'department',
        ]);
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 3,
            'is_mandatory' => false,
            'weight' => 5.0,
        ]);

        $skilledDept = Department::factory()->create();
        $skilledEmployee = $this->activeEmployee();
        $skilledEmployee->skills()->attach($skill->id, ['proficiency_level' => 5]);
        $skilledDept->employees()->save($skilledEmployee);

        $unskilledDept = Department::factory()->create();
        $unskilledDept->employees()->save($this->activeEmployee());

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertEquals($skilledDept->id, $recs->first()->department_id);
        $this->assertGreaterThan($recs->last()->total_score, $recs->first()->total_score);
    }

    public function test_criteria_snapshot_stores_assignment_mode(): void
    {
        $admin = $this->admin();
        $project = Project::factory()->create([
            'created_by' => $admin->id,
            'assignment_mode' => 'team',
        ]);
        Team::factory()->create();

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertEquals('team', $run->criteria_snapshot['assignment_mode']);
    }

    public function test_candidate_missing_mandatory_skill_is_excluded(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $skill = Skill::factory()->create();
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 3,
            'is_mandatory' => true,
            'weight' => 1.0,
        ]);

        $skilled = $this->activeEmployee();
        $skilled->skills()->attach($skill->id, ['proficiency_level' => 5]);

        $unskilled = $this->activeEmployee();

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($skilled->id, $recs->first()->employee_id);
    }

    public function test_candidate_below_mandatory_proficiency_is_excluded(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $skill = Skill::factory()->create();
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 4,
            'is_mandatory' => true,
            'weight' => 1.0,
        ]);

        $below = $this->activeEmployee();
        $below->skills()->attach($skill->id, ['proficiency_level' => 2]);

        $meets = $this->activeEmployee();
        $meets->skills()->attach($skill->id, ['proficiency_level' => 5]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($meets->id, $recs->first()->employee_id);
    }

    public function test_expired_certification_is_not_counted(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $cert = Certification::factory()->create();
        $project->requiredCertifications()->attach($cert->id, ['is_mandatory' => false]);

        $withValid = $this->activeEmployee();
        $withValid->certifications()->attach($cert->id, [
            'verification_status' => 'verified',
            'expires_at' => now()->addYear(),
        ]);

        $withExpired = $this->activeEmployee();
        $withExpired->certifications()->attach($cert->id, [
            'verification_status' => 'verified',
            'expires_at' => now()->subDay(),
        ]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertEquals($withValid->id, $recs->first()->employee_id);
        $this->assertEquals(1, $recs->first()->explanation['certifications']['matched']);
        $this->assertEquals(0, $recs->last()->explanation['certifications']['matched']);
    }

    public function test_expired_certification_blocks_mandatory_requirement(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $cert = Certification::factory()->create();
        $project->requiredCertifications()->attach($cert->id, ['is_mandatory' => true]);

        $withExpired = $this->activeEmployee();
        $withExpired->certifications()->attach($cert->id, [
            'verification_status' => 'verified',
            'expires_at' => now()->subDay(),
        ]);

        $withValid = $this->activeEmployee();
        $withValid->certifications()->attach($cert->id, [
            'verification_status' => 'verified',
            'expires_at' => null,
        ]);

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($withValid->id, $recs->first()->employee_id);
    }

    public function test_missing_availability_record_uses_neutral_score(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $documented = $this->activeEmployee();
        EmployeeAvailability::create([
            'employee_id' => $documented->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'availability_percentage' => 100,
            'status' => 'available',
        ]);

        $unreported = $this->activeEmployee();

        $run = app(RecommendationService::class)->run($project, $admin);

        $recs = Recommendation::where('recommendation_run_id', $run->id)
            ->orderBy('rank')
            ->get();

        $this->assertEquals($documented->id, $recs->first()->employee_id);
        $unreportedRec = $recs->firstWhere('employee_id', $unreported->id);
        $this->assertEquals(0.5, $unreportedRec->explanation['availability']);

        $activeWeights = 0.15 + 0.10;
        $expected = ((0.5 * 0.15) + (1.0 * 0.10)) / $activeWeights;
        $this->assertEqualsWithDelta($expected, $unreportedRec->total_score, 0.001);
    }

    public function test_audit_log_records_mandatory_exclusions(): void
    {
        $project = $this->project();
        $admin = $this->admin();

        $skill = Skill::factory()->create();
        $project->requiredSkills()->attach($skill->id, [
            'minimum_proficiency' => 3,
            'is_mandatory' => true,
            'weight' => 1.0,
        ]);

        $skilled = $this->activeEmployee();
        $skilled->skills()->attach($skill->id, ['proficiency_level' => 5]);
        $this->activeEmployee(); // eligible but excluded by mandatory gate

        $run = app(RecommendationService::class)->run($project, $admin);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'recommendation.run',
            'auditable_type' => RecommendationRun::class,
            'auditable_id' => $run->id,
        ]);

        $log = AuditLog::where('action', 'recommendation.run')
            ->where('auditable_id', $run->id)
            ->firstOrFail();

        $this->assertEquals(1, $log->new_values['excluded_by_mandatory']);
    }
}
