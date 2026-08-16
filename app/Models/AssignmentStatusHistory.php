<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $assignment_id
 * @property AssignmentStatus|null $old_status
 * @property AssignmentStatus $new_status
 * @property int $changed_by
 * @property string|null $reason
 * @property Carbon|CarbonImmutable|null $created_at
 */
#[Fillable(['assignment_id', 'old_status', 'new_status', 'changed_by', 'reason'])]
class AssignmentStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'assignment_status_history';

    protected function casts(): array
    {
        return [
            'old_status' => AssignmentStatus::class,
            'new_status' => AssignmentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $history) {
            $history->created_at = $history->created_at ?? now();
        });
    }

    /** @return BelongsTo<Assignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
