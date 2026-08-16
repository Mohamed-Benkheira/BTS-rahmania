<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $team_id
 * @property string|null $responsibility
 * @property float|null $allocation_percentage
 */
class AssignmentTeam extends Pivot
{
    protected $table = 'assignment_teams';

    protected function casts(): array
    {
        return [
            'allocation_percentage' => 'float',
        ];
    }

    /** @return BelongsTo<Assignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
