<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Models\Certification;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\Language;
use App\Models\ProfileChangeRequest;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\ProfileChangeRequested;
use App\Notifications\ProfileChangeResolved;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileChangeRequestService
{
    private const PROFILE_FIELDS = ['phone', 'biography', 'profile_photo_path', 'birth_date'];

    private AuditService $audit;

    public function __construct(?AuditService $audit = null)
    {
        $this->audit = $audit ?? app(AuditService::class);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function submit(
        Employee $employee,
        ProfileChangeType $type,
        ?int $subjectId = null,
        array $payload = [],
        ?string $note = null,
        ?User $actor = null,
    ): ProfileChangeRequest {
        $this->validate($type, $subjectId, $payload);

        $request = ProfileChangeRequest::create([
            'employee_id' => $employee->id,
            'type' => $type->value,
            'subject_id' => $subjectId,
            'payload' => $payload,
            'previous' => $this->snapshot($employee, $type, $subjectId, $payload),
            'status' => ProfileChangeStatus::Pending->value,
            'submitted_note' => $note,
        ]);

        $this->audit->log('profile-change.requested', $request, null, ['payload' => $payload], $actor);

        app(NotificationService::class)->toReviewers(new ProfileChangeRequested($request), $actor);

        return $request->fresh();
    }

    public function approve(ProfileChangeRequest $request, ?User $reviewer = null, ?string $note = null): ProfileChangeRequest
    {
        $this->assertPending($request);
        $this->assertCanReview($reviewer);

        $this->applyChange($request, $reviewer);

        $request->forceFill([
            'status' => ProfileChangeStatus::Approved->value,
            'reviewer_id' => $reviewer->id ?? auth()->id(),
            'reviewer_note' => $note,
            'reviewed_at' => now(),
        ])->save();

        $this->audit->log('profile-change.approved', $request, ['status' => ProfileChangeStatus::Pending->value], ['status' => ProfileChangeStatus::Approved->value, 'note' => $note], $reviewer);

        $this->notifyResolved($request);

        return $request->fresh();
    }

    public function reject(ProfileChangeRequest $request, ?User $reviewer = null, ?string $note = null): ProfileChangeRequest
    {
        $this->assertPending($request);
        $this->assertCanReview($reviewer);

        $request->forceFill([
            'status' => ProfileChangeStatus::Rejected->value,
            'reviewer_id' => $reviewer->id ?? auth()->id(),
            'reviewer_note' => $note,
            'reviewed_at' => now(),
        ])->save();

        $this->audit->log('profile-change.rejected', $request, ['status' => ProfileChangeStatus::Pending->value], ['status' => ProfileChangeStatus::Rejected->value, 'note' => $note], $reviewer);

        $this->notifyResolved($request);

        return $request->fresh();
    }

    public function canReview(User $user): bool
    {
        return $user->can('approve profile change requests');
    }

    private function applyChange(ProfileChangeRequest $request, ?User $reviewer): void
    {
        $employee = $request->employee;
        $payload = $request->payload;

        match ($request->type) {
            ProfileChangeType::Profile => $employee->update(array_intersect_key($payload, array_flip(self::PROFILE_FIELDS))),
            ProfileChangeType::Skill => $this->applySkill($employee, $request->subject_id, $payload, $reviewer),
            ProfileChangeType::Language => $this->applyLanguage($employee, $request->subject_id, $payload),
            ProfileChangeType::Certification => $this->applyCertification($employee, $request->subject_id, $payload),
            ProfileChangeType::Availability => $this->applyAvailability($employee, $payload),
        };
    }

    /** @param array<string, mixed> $payload */
    private function applySkill(Employee $employee, ?int $skillId, array $payload, ?User $reviewer): void
    {
        $attributes = [
            'proficiency_level' => $payload['proficiency_level'] ?? 3,
            'years_experience' => $payload['years_experience'] ?? null,
            'last_used_at' => $payload['last_used_at'] ?? now()->toDateString(),
            'notes' => $payload['notes'] ?? null,
            'verified_at' => now(),
            'verified_by' => $reviewer?->id ?? auth()->id(),
        ];

        if ($employee->skills()->wherePivot('skill_id', $skillId)->exists()) {
            $employee->skills()->updateExistingPivot($skillId, $attributes);
        } else {
            $employee->skills()->attach($skillId, $attributes);
        }
    }

    /** @param array<string, mixed> $payload */
    private function applyLanguage(Employee $employee, ?int $languageId, array $payload): void
    {
        $attributes = [
            'speaking_level' => $payload['speaking_level'] ?? null,
            'writing_level' => $payload['writing_level'] ?? null,
            'reading_level' => $payload['reading_level'] ?? null,
        ];

        if ($employee->languages()->wherePivot('language_id', $languageId)->exists()) {
            $employee->languages()->updateExistingPivot($languageId, $attributes);
        } else {
            $employee->languages()->attach($languageId, $attributes);
        }
    }

    /** @param array<string, mixed> $payload */
    private function applyCertification(Employee $employee, ?int $certificationId, array $payload): void
    {
        $attributes = [
            'certificate_number' => $payload['certificate_number'] ?? null,
            'issued_at' => $payload['issued_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'document_path' => $payload['document_path'] ?? null,
            'verification_status' => 'verified',
        ];

        if ($employee->certifications()->wherePivot('certification_id', $certificationId)->exists()) {
            $employee->certifications()->updateExistingPivot($certificationId, $attributes);
        } else {
            $employee->certifications()->attach($certificationId, $attributes);
        }
    }

    /** @param array<string, mixed> $payload */
    private function applyAvailability(Employee $employee, array $payload): void
    {
        $percentage = (float) ($payload['availability_percentage'] ?? 100);

        EmployeeAvailability::create([
            'employee_id' => $employee->id,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
            'availability_percentage' => $percentage,
            'status' => $payload['status'] ?? $this->statusForPercentage($percentage),
            'reason' => $payload['reason'] ?? null,
        ]);
    }

    private function statusForPercentage(float $percentage): string
    {
        return match (true) {
            $percentage <= 0 => AvailabilityStatus::OnLeave->value,
            $percentage < 100 => AvailabilityStatus::PartiallyAvailable->value,
            default => AvailabilityStatus::Available->value,
        };
    }

    /** @param array<string, mixed> $payload */
    private function snapshot(Employee $employee, ProfileChangeType $type, ?int $subjectId, array $payload): ?array
    {
        return match ($type) {
            ProfileChangeType::Profile => array_intersect_key($employee->only(self::PROFILE_FIELDS), $payload),
            ProfileChangeType::Skill => optional($employee->skills()->wherePivot('skill_id', $subjectId)->first()?->pivot)
                ->only(['proficiency_level', 'years_experience', 'last_used_at', 'notes']),
            ProfileChangeType::Language => optional($employee->languages()->wherePivot('language_id', $subjectId)->first()?->pivot)
                ->only(['speaking_level', 'writing_level', 'reading_level', 'language_id']),
            ProfileChangeType::Certification => optional($employee->certifications()->wherePivot('certification_id', $subjectId)->first()?->pivot)
                ->only(['certificate_number', 'issued_at', 'expires_at', 'document_path', 'verification_status']),
            ProfileChangeType::Availability => optional($employee->availabilities()->orderByDesc('start_date')->first())
                ->only(['start_date', 'end_date', 'availability_percentage', 'status', 'reason']),
        };
    }

    /** @param array<string, mixed> $payload */
    private function validate(ProfileChangeType $type, ?int $subjectId, array $payload): void
    {
        if ($type === ProfileChangeType::Skill) {
            $this->assertSubjectExists(Skill::class, $subjectId, 'skill');
        }

        if ($type === ProfileChangeType::Language) {
            $this->assertSubjectExists(Language::class, $subjectId, 'language');
        }

        if ($type === ProfileChangeType::Certification) {
            $this->assertSubjectExists(Certification::class, $subjectId, 'certification');
        }

        $rules = match ($type) {
            ProfileChangeType::Profile => [
                'phone' => ['nullable', 'string', 'max:255'],
                'biography' => ['nullable', 'string', 'max:2000'],
                'profile_photo_path' => ['nullable', 'string', 'max:255'],
                'birth_date' => ['nullable', 'date'],
            ],
            ProfileChangeType::Skill => [
                'proficiency_level' => ['required', 'integer', 'between:1,5'],
                'years_experience' => ['nullable', 'numeric', 'min:0'],
                'last_used_at' => ['nullable', 'date'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            ProfileChangeType::Language => [
                'speaking_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'native'])],
                'writing_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'native'])],
                'reading_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced', 'native'])],
            ],
            ProfileChangeType::Certification => [
                'certificate_number' => ['nullable', 'string', 'max:255'],
                'issued_at' => ['nullable', 'date'],
                'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
                'document_path' => ['nullable', 'string', 'max:2048'],
            ],
            ProfileChangeType::Availability => [
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'availability_percentage' => ['required', 'numeric', 'between:0,100'],
                'reason' => ['nullable', 'string', 'max:500'],
            ],
        };

        Validator::make($payload, $rules)->validate();
    }

    private function assertSubjectExists(string $model, ?int $subjectId, string $label): void
    {
        if ($subjectId === null || $model::whereKey($subjectId)->doesntExist()) {
            Validator::make([$label => $subjectId], [$label => ['required', 'exists:'.(new $model)->getTable().',id']])->validate();
        }
    }

    private function assertPending(ProfileChangeRequest $request): void
    {
        if ($request->status !== ProfileChangeStatus::Pending) {
            throw new \DomainException('This change request has already been reviewed.');
        }
    }

    private function assertCanReview(?User $reviewer): void
    {
        $user = $reviewer ?? auth()->user();

        if ($user === null || ! $this->canReview($user)) {
            throw new \DomainException('You are not allowed to review profile change requests.');
        }
    }

    private function notifyResolved(ProfileChangeRequest $request): void
    {
        if ($request->employee->user !== null) {
            $request->employee->user->notify(new ProfileChangeResolved($request));
        }
    }
}
