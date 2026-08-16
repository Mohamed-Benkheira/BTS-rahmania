<?php

namespace Tests\Feature\Domain;

use App\Enums\CertificationVerificationStatus;
use App\Enums\LanguageLevel;
use App\Models\BusinessUnit;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\EmployeeWorkload;
use App\Models\Language;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_belongs_to_organization_hierarchy(): void
    {
        $businessUnit = BusinessUnit::factory()->create();
        $department = Department::factory()->create(['business_unit_id' => $businessUnit->id]);
        $team = Team::factory()->create(['department_id' => $department->id]);
        $position = Position::factory()->create();
        $manager = Employee::factory()->active()->create();

        $employee = Employee::factory()->active()->create([
            'business_unit_id' => $businessUnit->id,
            'department_id' => $department->id,
            'team_id' => $team->id,
            'position_id' => $position->id,
            'manager_id' => $manager->id,
        ]);

        $this->assertTrue($employee->businessUnit->is($businessUnit));
        $this->assertTrue($employee->department->is($department));
        $this->assertTrue($employee->team->is($team));
        $this->assertTrue($employee->position->is($position));
        $this->assertTrue($employee->manager->is($manager));
        $this->assertTrue($manager->subordinates->contains($employee));
    }

    public function test_employee_has_skills_certifications_languages_with_pivot_data(): void
    {
        $employee = Employee::factory()->active()->create();
        $skill = Skill::factory()->create();
        $certification = Certification::factory()->create();
        $language = Language::factory()->create();

        $employee->skills()->attach($skill, [
            'proficiency_level' => 4,
            'years_experience' => 3.5,
        ]);
        $employee->certifications()->attach($certification, [
            'verification_status' => 'verified',
        ]);
        $employee->languages()->attach($language, [
            'speaking_level' => 'native',
        ]);

        $employee->refresh();

        $this->assertSame(4, $employee->skills()->first()->pivot->proficiency_level);
        $this->assertSame(3.5, (float) $employee->skills()->first()->pivot->years_experience);
        $this->assertSame(
            CertificationVerificationStatus::Verified,
            $employee->certifications()->first()->pivot->verification_status,
        );
        $this->assertSame(
            LanguageLevel::Native,
            $employee->languages()->first()->pivot->speaking_level,
        );
    }

    public function test_employee_availability_and_workload_by_date_range(): void
    {
        $employee = Employee::factory()->active()->create();

        EmployeeAvailability::create([
            'employee_id' => $employee->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'availability_percentage' => 80,
            'status' => 'partially_available',
        ]);

        EmployeeWorkload::create([
            'employee_id' => $employee->id,
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
            'allocated_percentage' => 40,
            'allocated_hours' => 100,
            'source' => 'manual',
        ]);

        $this->assertCount(1, $employee->availabilities);
        $this->assertCount(1, $employee->workloads);
        $this->assertSame(40, (int) $employee->workloads->first()->allocated_percentage);
    }

    public function test_project_has_requirements_and_required_traits(): void
    {
        $project = Project::factory()->create();
        $skill = Skill::factory()->create();
        $certification = Certification::factory()->create();
        $language = Language::factory()->create();

        ProjectRequirement::create([
            'project_id' => $project->id,
            'title' => 'Fluent in PostgreSQL',
            'requirement_type' => 'skill',
            'is_mandatory' => true,
            'weight' => 1.0,
        ]);

        $project->requiredSkills()->attach($skill, [
            'minimum_proficiency' => 3,
            'is_mandatory' => true,
            'weight' => 0.9,
        ]);
        $project->requiredCertifications()->attach($certification, ['is_mandatory' => true]);
        $project->requiredLanguages()->attach($language, ['minimum_level' => 'intermediate', 'is_mandatory' => false]);

        $project->refresh();

        $this->assertCount(1, $project->requirements);
        $this->assertCount(1, $project->requiredSkills);
        $this->assertCount(1, $project->requiredCertifications);
        $this->assertCount(1, $project->requiredLanguages);
        $this->assertSame(3, $project->requiredSkills()->first()->pivot->minimum_proficiency);
        $this->assertSame(
            LanguageLevel::Intermediate,
            $project->requiredLanguages()->first()->pivot->minimum_level,
        );
    }

    public function test_assignment_members_and_teams(): void
    {
        $project = Project::factory()->create();
        $assignment = $project->assignments()->create([
            'assigned_by' => User::factory()->create()->id,
            'assignment_type' => 'employee',
            'status' => 'pending',
            'assigned_at' => now(),
        ]);

        $employee = Employee::factory()->active()->create();
        $team = Team::factory()->create();

        $assignment->members()->attach($employee, ['allocation_percentage' => 50]);
        $assignment->teams()->attach($team, ['allocation_percentage' => 100]);

        $this->assertTrue($assignment->members->contains($employee));
        $this->assertTrue($assignment->teams->contains($team));
    }

    public function test_user_has_employee_profile_and_multiple_roles(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'project-manager']);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->assignRole('project-manager');

        Employee::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('project-manager'));
        $this->assertNotNull($user->employee);
    }
}
