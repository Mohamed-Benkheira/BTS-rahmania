<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $skill_id
 * @property int|null $proficiency_level
 * @property float|null $years_experience
 * @property Carbon|null $last_used_at
 * @property Carbon|null $verified_at
 * @property int|null $verified_by
 * @property string|null $notes
 */
class EmployeeSkill extends Pivot
{
    protected $table = 'employee_skills';

    protected function casts(): array
    {
        return [
            'last_used_at' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
