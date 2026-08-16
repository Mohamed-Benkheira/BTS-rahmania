<?php

namespace Tests\Feature\Domain;

use App\Enums\EmploymentStatus;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function project(): Project
    {
        return Project::factory()->create(['created_by' => $this->admin()->id]);
    }

    private function activeEmployee(): Employee
    {
        return Employee::factory()->active()->create();
    }

    public function test_create_starts_pending_and_records_history(): void
    {
        $project = $this->project();

        $assignment = app(AssignmentService::class)->actor($this->admin())
            ->create($project);

        $this->assertEquals('pending', $assignment->status->value);
        $this->assertDatabaseHas('assignment_status_history', [
            'assignment_id' => $assignment->id,
            'new_status' => 'pending',
            'old_status' => null,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Assignment::class,
            'auditable_id' => $assignment->id,
            'action' => 'assignment.created',
        ]);
    }

    public function test_cannot_create_second_current_assignment_for_project(): void
    {
        $project = $this->project();
        $service = app(AssignmentService::class)->actor($this->admin());

        $assignment = $service->create($project);
        $service->approve($assignment);

        $this->expectException(\DomainException::class);
        $service->create($project);
    }

    public function test_inactive_employee_cannot_be_assigned(): void
    {
        $project = $this->project();
        $inactive = Employee::factory()->create(['employment_status' => EmploymentStatus::OnLeave]);
        $service = app(AssignmentService::class)->actor($this->admin());

        $assignment = $service->create($project);

        $this->expectException(\DomainException::class);
        $service->assignMembers($assignment, [[$inactive, 'allocation_percentage' => 50]]);
    }

    public function test_active_employee_can_be_assigned(): void
    {
        $project = $this->project();
        $employee = $this->activeEmployee();
        $service = app(AssignmentService::class)->actor($this->admin());

        $assignment = $service->create($project);
        $service->assignMembers($assignment, [[$employee, 'allocation_percentage' => 50]]);

        $this->assertCount(1, $assignment->members);
        $this->assertSame(50.0, (float) $assignment->members()->first()->pivot->allocation_percentage);
    }

    public function test_valid_status_transitions_are_recorded(): void
    {
        $service = app(AssignmentService::class)->actor($this->admin());
        $assignment = $service->create($this->project());

        $service->approve($assignment);
        $service->activate($assignment);
        $service->complete($assignment);

        $this->assertEquals('completed', $assignment->status->value);
        $this->assertCount(4, $assignment->statusHistory);

        $history = $assignment->statusHistory->pluck('new_status.value')->all();
        $this->assertSame(['pending', 'approved', 'active', 'completed'], $history);

        $this->assertDatabaseHas('audit_logs', ['action' => 'assignment.approved']);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $service = app(AssignmentService::class)->actor($this->admin());
        $assignment = $service->create($this->project());

        $this->expectException(\DomainException::class);
        $service->complete($assignment);
    }

    public function test_completion_sets_left_at_on_members(): void
    {
        $employee = $this->activeEmployee();
        $service = app(AssignmentService::class)->actor($this->admin());
        $assignment = $service->create($this->project());
        $service->assignMembers($assignment, [$employee->id]);

        $service->approve($assignment);
        $service->activate($assignment);
        $service->complete($assignment);

        $this->assertNotNull($assignment->members()->first()->pivot->left_at);
    }

    public function test_cancellation_allowed_from_pending_approved_active(): void
    {
        $service = app(AssignmentService::class)->actor($this->admin());
        $assignment = $service->create($this->project());

        $service->cancel($assignment);

        $this->assertEquals('cancelled', $assignment->status->value);
        $this->assertDatabaseHas('assignment_status_history', [
            'assignment_id' => $assignment->id,
            'old_status' => 'pending',
            'new_status' => 'cancelled',
        ]);
    }

    public function test_actor_is_recorded_in_history_and_audit(): void
    {
        $admin = $this->admin();
        $assignment = app(AssignmentService::class)->actor($admin)->create($this->project());

        $this->assertDatabaseHas('assignment_status_history', [
            'assignment_id' => $assignment->id,
            'changed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'assignment.created',
        ]);
    }
}
