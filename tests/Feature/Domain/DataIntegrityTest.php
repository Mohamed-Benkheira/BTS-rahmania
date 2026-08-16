<?php

namespace Tests\Feature\Domain;

use App\Enums\AssignmentStatus;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_approved_or_active_assignment_per_project(): void
    {
        $project = Project::factory()->create();

        $project->assignments()->create([
            'assigned_by' => User::factory()->create()->id,
            'assignment_type' => 'employee',
            'status' => AssignmentStatus::Approved->value,
            'assigned_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        $project->assignments()->create([
            'assigned_by' => User::factory()->create()->id,
            'assignment_type' => 'employee',
            'status' => AssignmentStatus::Active->value,
            'assigned_at' => now(),
        ]);
    }

    public function test_multiple_historical_assignments_are_allowed_per_project(): void
    {
        $project = Project::factory()->create();

        $project->assignments()->create([
            'assigned_by' => User::factory()->create()->id,
            'assignment_type' => 'employee',
            'status' => AssignmentStatus::Pending->value,
            'assigned_at' => now(),
        ]);

        $project->assignments()->create([
            'assigned_by' => User::factory()->create()->id,
            'assignment_type' => 'employee',
            'status' => AssignmentStatus::Completed->value,
            'assigned_at' => now(),
        ]);

        $this->assertSame(2, $project->assignments()->count());
    }

    public function test_linked_user_cannot_be_deleted_while_employee_profile_exists(): void
    {
        $user = User::factory()->create();
        Employee::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);

        $user->delete();
    }

    public function test_unlinked_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_employee_creation_is_audited(): void
    {
        $employee = Employee::factory()->create();

        $log = AuditLog::where('action', 'employee.created')
            ->where('auditable_type', Employee::class)
            ->where('auditable_id', $employee->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($employee->first_name, $log->new_values['first_name'] ?? null);
    }

    public function test_employee_update_is_audited_with_old_and_new_values(): void
    {
        $employee = Employee::factory()->create();

        $employee->update(['phone' => '123456789']);

        $log = AuditLog::where('action', 'employee.updated')
            ->where('auditable_id', $employee->id)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('phone', $log->new_values);
        $this->assertSame('123456789', $log->new_values['phone']);
    }

    public function test_project_creation_is_audited(): void
    {
        $project = Project::factory()->create();

        $log = AuditLog::where('action', 'project.created')
            ->where('auditable_type', Project::class)
            ->where('auditable_id', $project->id)
            ->first();

        $this->assertNotNull($log);
    }
}
