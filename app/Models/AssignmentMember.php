<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $employee_id
 * @property string|null $responsibility
 * @property float|null $allocation_percentage
 * @property float|null $allocated_hours
 * @property Carbon|null $joined_at
 * @property Carbon|null $left_at
 */
class AssignmentMember extends Pivot
{
    protected $table = 'assignment_members';

    protected function casts(): array
    {
        return [
            'allocation_percentage' => 'float',
            'allocated_hours' => 'float',
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    /** @return BelongsTo<Assignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
