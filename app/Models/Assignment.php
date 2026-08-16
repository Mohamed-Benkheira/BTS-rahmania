<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $assigned_by
 * @property int|null $approved_by
 * @property Project|null $project
 * @property User|null $assigner
 * @property AssignmentType $assignment_type
 * @property AssignmentStatus $status
 * @property Carbon|null $assigned_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $notes
 */
#[Fillable([
    'project_id',
    'assigned_by',
    'approved_by',
    'assignment_type',
    'status',
    'assigned_at',
    'approved_at',
    'start_date',
    'end_date',
    'notes',
])]
class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'assignment_type' => AssignmentType::class,
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
            'approved_at' => 'datetime',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsToMany<Employee, $this, AssignmentMember> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'assignment_members')
            ->using(AssignmentMember::class)
            ->withPivot(['responsibility', 'allocation_percentage', 'allocated_hours', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Team, $this, AssignmentTeam> */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'assignment_teams')
            ->using(AssignmentTeam::class)
            ->withPivot(['responsibility', 'allocation_percentage'])
            ->withTimestamps();
    }

    /** @return HasMany<AssignmentStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(AssignmentStatusHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === AssignmentStatus::Active;
    }

    public function getTitleAttribute(): string
    {
        return "#{$this->id} {$this->project?->name}";
    }
}
