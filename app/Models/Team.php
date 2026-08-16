<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $department_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int|null $team_leader_id
 * @property bool $is_active
 */
#[Fillable(['department_id', 'name', 'code', 'description', 'team_leader_id', 'is_active'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'team_leader_id');
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /** @return BelongsToMany<Assignment, $this, AssignmentTeam> */
    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_teams')
            ->using(AssignmentTeam::class)
            ->withPivot(['responsibility', 'allocation_percentage'])
            ->withTimestamps();
    }
}
