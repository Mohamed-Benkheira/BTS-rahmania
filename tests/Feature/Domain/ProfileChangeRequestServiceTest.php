<?php

namespace Tests\Feature\Domain;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Models\Certification;
use App\Models\Employee;
use App\Models\Language;
use App\Models\ProfileChangeRequest;
use App\Models\Skill;
use App\Models\User;
use App\Services\ProfileChangeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileChangeRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private function employee(): Employee
    {
        return Employee::factory()->active()->create();
    }

    private function reviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'hr']));
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'approve profile change requests']));

        return $user;
    }

    public function test_submit_profile_request_snapshots_previous_and_creates_pending(): void
    {
        $employee = $this->employee();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 123 456'],
        );

        $this->assertSame(ProfileChangeStatus::Pending, $request->status);
        $this->assertSame(['phone' => $employee->phone], $request->previous);
        $this->assertSame(['phone' => '+213 555 123 456'], $request->payload);
    }

    public function test_submit_requires_valid_subject_for_skill(): void
    {
        $this->expectException(ValidationException::class);

        app(ProfileChangeRequestService::class)->submit(
            employee: $this->employee(),
            type: ProfileChangeType::Skill,
            payload: ['proficiency_level' => 4],
        );
    }

    public function test_approve_applies_profile_fields(): void
    {
        $employee = $this->employee();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 123 456', 'biography' => 'Senior engineer'],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer, 'Confirmed');

        $this->assertSame(ProfileChangeStatus::Approved, $request->fresh()->status);
        $this->assertSame('+213 555 123 456', $employee->fresh()->phone);
        $this->assertSame('Senior engineer', $employee->fresh()->biography);
        $this->assertSame($reviewer->id, $request->fresh()->reviewer_id);
        $this->assertSame('Confirmed', $request->fresh()->reviewer_note);
        $this->assertNotNull($request->fresh()->reviewed_at);
    }

    public function test_approve_applies_skill_as_verified(): void
    {
        $employee = $this->employee();
        $skill = Skill::factory()->create();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Skill,
            subjectId: $skill->id,
            payload: ['proficiency_level' => 5, 'years_experience' => 8],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $pivot = $employee->skills()->wherePivot('skill_id', $skill->id)->first()->pivot;

        $this->assertSame(5, (int) $pivot->proficiency_level);
        $this->assertSame('8.00', (string) $pivot->years_experience);
        $this->assertNotNull($pivot->verified_at);
        $this->assertSame($reviewer->id, $pivot->verified_by);
    }

    public function test_approve_applies_certification_as_verified(): void
    {
        $employee = $this->employee();
        $certification = Certification::factory()->create();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Certification,
            subjectId: $certification->id,
            payload: [
                'certificate_number' => 'CERT-123',
                'issued_at' => '2024-01-01',
                'expires_at' => '2027-01-01',
                'document_path' => 'certification-documents/example.pdf',
            ],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $pivot = $employee->certifications()->wherePivot('certification_id', $certification->id)->first()->pivot;

        $this->assertSame('CERT-123', $pivot->certificate_number);
        $this->assertSame('verified', $pivot->verification_status->value);
        $this->assertSame('certification-documents/example.pdf', $pivot->document_path);
    }

    public function test_approve_applies_availability_latest_wins(): void
    {
        $employee = $this->employee();
        $reviewer = $this->reviewer();

        $first = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Availability,
            payload: ['start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'availability_percentage' => 30],
        );

        $second = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Availability,
            payload: ['start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'availability_percentage' => 100],
        );

        app(ProfileChangeRequestService::class)->approve($first, $reviewer);
        app(ProfileChangeRequestService::class)->approve($second, $reviewer);

        $this->assertSame(2, $employee->availabilities()->count());

        $latest = $employee->availabilities()->orderByDesc('start_date')->first();

        $this->assertSame('2026-02-01', $latest->start_date->toDateString());
        $this->assertSame(100.0, (float) $latest->availability_percentage);
        $this->assertSame('available', $latest->status->value);
    }

    public function test_reject_does_not_apply_change(): void
    {
        $employee = $this->employee();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 999 999'],
        );

        app(ProfileChangeRequestService::class)->reject($request, $reviewer, 'Not allowed');

        $this->assertSame(ProfileChangeStatus::Rejected, $request->fresh()->status);
        $this->assertNotSame('+213 555 999 999', $employee->fresh()->phone);
        $this->assertSame('Not allowed', $request->fresh()->reviewer_note);
    }

    public function test_cannot_review_without_permission(): void
    {
        $employee = $this->employee();
        $employeeUser = User::factory()->create();
        $employeeUser->assignRole(Role::firstOrCreate(['name' => 'employee']));

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 111 111'],
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('not allowed to review');

        app(ProfileChangeRequestService::class)->approve($request, $employeeUser);
    }

    public function test_cannot_review_twice(): void
    {
        $employee = $this->employee();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 111 111'],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('already been reviewed');

        app(ProfileChangeRequestService::class)->reject($request, $reviewer);
    }

    public function test_submit_and_approve_create_audit_logs(): void
    {
        $employee = $this->employee();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 222 222'],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'profile-change.requested',
            'auditable_type' => ProfileChangeRequest::class,
            'auditable_id' => $request->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'profile-change.approved',
            'auditable_type' => ProfileChangeRequest::class,
            'auditable_id' => $request->id,
        ]);
    }

    public function test_submit_notifies_reviewers_transitions_notify_employee(): void
    {
        $employeeUser = User::factory()->create();
        $employeeUser->assignRole(Role::firstOrCreate(['name' => 'employee']));

        $employee = $this->employee();
        $employee->user_id = $employeeUser->id;
        $employee->save();

        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Profile,
            payload: ['phone' => '+213 555 333 333'],
        );

        $this->assertSame(1, $reviewer->notifications()->count());

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $this->assertSame(1, $reviewer->notifications()->count(), 'no duplicate reviewer notifications');
        $this->assertSame(1, $employeeUser->notifications()->count(), 'employee received resolution notification');
        $this->assertSame('approved', $employeeUser->notifications()->first()->data['status']);
    }

    public function test_available_type_requires_foreign_key_subject(): void
    {
        $employee = $this->employee();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Language,
            subjectId: Language::factory()->create()->id,
            payload: ['speaking_level' => 'native'],
        );

        $this->assertSame(ProfileChangeType::Language, $request->type);
    }

    public function test_language_approval_attaches_levels(): void
    {
        $employee = $this->employee();
        $language = Language::factory()->create();
        $reviewer = $this->reviewer();

        $request = app(ProfileChangeRequestService::class)->submit(
            employee: $employee,
            type: ProfileChangeType::Language,
            subjectId: $language->id,
            payload: ['speaking_level' => 'advanced', 'reading_level' => 'native'],
        );

        app(ProfileChangeRequestService::class)->approve($request, $reviewer);

        $pivot = $employee->languages()->wherePivot('language_id', $language->id)->first()->pivot;

        $this->assertSame('advanced', $pivot->speaking_level->value);
        $this->assertSame('native', $pivot->reading_level->value);
        $this->assertNull($pivot->writing_level);
    }
}
