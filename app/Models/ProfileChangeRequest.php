<?php

namespace App\Models;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use Database\Factories\ProfileChangeRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property ProfileChangeType $type
 * @property int|null $subject_id
 * @property array<string, mixed> $payload
 * @property array<string, mixed>|null $previous
 * @property ProfileChangeStatus $status
 * @property string|null $submitted_note
 * @property int|null $reviewer_id
 * @property string|null $reviewer_note
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['employee_id', 'type', 'subject_id', 'payload', 'previous', 'status', 'submitted_note', 'reviewer_id', 'reviewer_note', 'reviewed_at'])]
class ProfileChangeRequest extends Model
{
    /** @use HasFactory<ProfileChangeRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ProfileChangeType::class,
            'payload' => 'array',
            'previous' => 'array',
            'status' => ProfileChangeStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function subjectDisplay(): ?string
    {
        return match ($this->type) {
            ProfileChangeType::Profile => $this->employee->full_name,
            ProfileChangeType::Skill => $this->subject_id ? optional(Skill::find($this->subject_id))->name : null,
            ProfileChangeType::Language => $this->subject_id ? optional(Language::find($this->subject_id))->name : null,
            ProfileChangeType::Certification => $this->subject_id ? optional(Certification::find($this->subject_id))->name : null,
            ProfileChangeType::Availability => implode(' → ', array_filter([
                $this->payload['start_date'] ?? null,
                $this->payload['end_date'] ?? null,
            ])),
        };
    }
}
