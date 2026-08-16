<?php

namespace App\Services;

use App\Enums\AssignmentMode;
use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\EmploymentStatus;
use App\Models\Assignment;
use App\Models\AssignmentStatusHistory;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;

class AssignmentService
{
    private AuditService $audit;

    public function __construct(
        private ?User $actor = null,
        ?AuditService $audit = null,
    ) {
        $this->audit = $audit ?? app(AuditService::class);
    }

    public function actor(?User $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    /** @param array<string, mixed> $options */
    public function create(Project $project, array $options = []): Assignment
    {
        $this->assertNoCurrentAssignment($project);

        $assignment = $project->assignments()->create([
            'assigned_by' => $options['assigned_by'] ?? $this->actor->id ?? auth()->id(),
            'approved_by' => $options['approved_by'] ?? null,
            'assignment_type' => $options['assignment_type'] ?? $this->typeForMode($project),
            'status' => AssignmentStatus::Pending->value,
            'assigned_at' => $options['assigned_at'] ?? now(),
            'approved_at' => $options['approved_at'] ?? null,
            'start_date' => $options['start_date'] ?? $project->start_date,
            'end_date' => $options['end_date'] ?? $project->target_end_date,
            'notes' => $options['notes'] ?? null,
        ]);

        $this->recordHistory($assignment, null, AssignmentStatus::Pending);
        $this->audit->assignmentCreated($assignment, $this->actor);

        return $assignment;
    }

    private function typeForMode(Project $project): string
    {
        return match ($project->assignment_mode) {
            AssignmentMode::SingleEmployee,
            AssignmentMode::MultipleEmployees => AssignmentType::Employee->value,
            AssignmentMode::Team => AssignmentType::Team->value,
            AssignmentMode::Department => AssignmentType::Department->value,
        };
    }

    /**
     * @param  array<int, array<int|string, mixed>|Employee|int>  $members
     */
    public function assignMembers(Assignment $assignment, array $members = []): Assignment
    {
        $validated = [];

        foreach ($members as $member) {
            $employee = $this->resolveEmployee($member);
            $attributes = is_array($member) ? $member : [];

            if ($employee->employment_status !== EmploymentStatus::Active) {
                throw new \DomainException("Employee [{$employee->full_name}] is not active and cannot be assigned.");
            }

            $validated[$employee->id] = [
                'responsibility' => $attributes['responsibility'] ?? null,
                'allocation_percentage' => $attributes['allocation_percentage'] ?? null,
                'allocated_hours' => $attributes['allocated_hours'] ?? null,
                'joined_at' => $attributes['joined_at'] ?? now(),
                'left_at' => null,
            ];
        }

        if ($validated !== []) {
            $assignment->members()->attach($validated);
        }

        return $assignment->refresh();
    }

    /**
     * @param  array<int, array<int|string, mixed>|Team|int>  $teams
     */
    public function assignTeams(Assignment $assignment, array $teams = []): Assignment
    {
        $validated = [];

        foreach ($teams as $team) {
            $teamModel = $this->resolveTeam($team);
            $attributes = is_array($team) ? $team : [];

            $validated[$teamModel->id] = [
                'responsibility' => $attributes['responsibility'] ?? null,
                'allocation_percentage' => $attributes['allocation_percentage'] ?? null,
            ];
        }

        if ($validated !== []) {
            $assignment->teams()->attach($validated);
        }

        return $assignment->refresh();
    }

    /** @param Team|int|array<int|string, mixed> $team */
    private function resolveTeam(Team|int|array $team): Team
    {
        if ($team instanceof Team) {
            return $team;
        }

        if (is_array($team)) {
            $value = $team['team_id'] ?? $team['id'] ?? ($team[0] ?? null);

            if ($value instanceof Team) {
                return $value;
            }

            if (is_int($value) || is_string($value)) {
                return Team::findOrFail($value);
            }

            throw new \InvalidArgumentException('Team array must contain a team_id or Team instance.');
        }

        return Team::findOrFail($team);
    }

    /** @param Employee|int|array<int|string, mixed> $member */
    private function resolveEmployee(Employee|int|array $member): Employee
    {
        if ($member instanceof Employee) {
            return $member;
        }

        $value = $member;

        if (is_array($member)) {
            $value = $member['employee_id']
                ?? $member['id']
                ?? ($member[0] ?? $member['employee'] ?? null);
        }

        if ($value instanceof Employee) {
            return $value;
        }

        if (is_int($value) || is_string($value)) {
            return Employee::findOrFail($value);
        }

        throw new \InvalidArgumentException('Member array must contain an employee_id or Employee instance.');
    }

    public function approve(Assignment $assignment, ?string $reason = null): Assignment
    {
        $this->assertTransition($assignment, AssignmentStatus::Approved);
        $old = $assignment->status;

        $assignment->forceFill([
            'status' => AssignmentStatus::Approved->value,
            'approved_by' => $this->actor->id ?? auth()->id(),
            'approved_at' => now(),
        ])->save();

        $this->recordHistory($assignment, $old, AssignmentStatus::Approved, $reason);
        $this->audit->assignmentTransition($assignment, 'approved', $old->value, $this->actor);

        return $assignment->refresh();
    }

    public function activate(Assignment $assignment, ?string $reason = null): Assignment
    {
        $this->assertTransition($assignment, AssignmentStatus::Active);
        $old = $assignment->status;

        $assignment->forceFill(['status' => AssignmentStatus::Active->value])->save();

        $this->recordHistory($assignment, $old, AssignmentStatus::Active, $reason);
        $this->audit->assignmentTransition($assignment, 'activated', $old->value, $this->actor);

        return $assignment->refresh();
    }

    public function complete(Assignment $assignment, ?string $reason = null): Assignment
    {
        $this->assertTransition($assignment, AssignmentStatus::Completed);
        $old = $assignment->status;

        $assignment->forceFill(['status' => AssignmentStatus::Completed->value])->save();

        $assignment->members()->updateExistingPivot(
            $assignment->members()->pluck('employees.id')->all(),
            ['left_at' => Carbon::today()],
        );

        $this->recordHistory($assignment, $old, AssignmentStatus::Completed, $reason);
        $this->audit->assignmentTransition($assignment, 'completed', $old->value, $this->actor);

        return $assignment->refresh();
    }

    public function reject(Assignment $assignment, ?string $reason = null): Assignment
    {
        $this->assertTransition($assignment, AssignmentStatus::Rejected);
        $old = $assignment->status;

        $assignment->forceFill(['status' => AssignmentStatus::Rejected->value])->save();

        $this->recordHistory($assignment, $old, AssignmentStatus::Rejected, $reason);
        $this->audit->assignmentTransition($assignment, 'rejected', $old->value, $this->actor);

        return $assignment->refresh();
    }

    public function cancel(Assignment $assignment, ?string $reason = null): Assignment
    {
        if (! in_array($assignment->status, [AssignmentStatus::Pending, AssignmentStatus::Approved, AssignmentStatus::Active], true)) {
            throw new \DomainException("Cannot cancel an assignment in status [{$assignment->status->value}].");
        }

        $old = $assignment->status;

        $assignment->forceFill(['status' => AssignmentStatus::Cancelled->value])->save();

        $this->recordHistory($assignment, $old, AssignmentStatus::Cancelled, $reason);
        $this->audit->assignmentTransition($assignment, 'cancelled', $old->value, $this->actor);

        return $assignment->refresh();
    }

    private function assertNoCurrentAssignment(Project $project): void
    {
        $current = $project->assignments()
            ->whereIn('status', [AssignmentStatus::Approved->value, AssignmentStatus::Active->value])
            ->exists();

        if ($current) {
            throw new \DomainException("Project [{$project->name}] already has a current approved or active assignment.");
        }
    }

    private function assertTransition(Assignment $assignment, AssignmentStatus $new): void
    {
        $allowed = [
            AssignmentStatus::Pending->value => [AssignmentStatus::Approved, AssignmentStatus::Rejected, AssignmentStatus::Cancelled],
            AssignmentStatus::Approved->value => [AssignmentStatus::Active, AssignmentStatus::Cancelled],
            AssignmentStatus::Active->value => [AssignmentStatus::Completed, AssignmentStatus::Cancelled],
        ];

        $valid = $allowed[$assignment->status->value] ?? [];

        if (! in_array($new, $valid, true)) {
            throw new \DomainException("Cannot transition assignment from [{$assignment->status->value}] to [{$new->value}].");
        }
    }

    private function recordHistory(Assignment $assignment, ?AssignmentStatus $old, AssignmentStatus $new, ?string $reason = null): void
    {
        AssignmentStatusHistory::create([
            'assignment_id' => $assignment->id,
            'old_status' => $old?->value,
            'new_status' => $new->value,
            'changed_by' => $this->actor->id ?? auth()->id(),
            'reason' => $reason,
        ]);
    }
}
