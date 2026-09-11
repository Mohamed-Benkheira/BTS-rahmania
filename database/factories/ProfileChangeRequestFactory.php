<?php

namespace Database\Factories;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Models\Employee;
use App\Models\ProfileChangeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileChangeRequest>
 */
class ProfileChangeRequestFactory extends Factory
{
    protected $model = ProfileChangeRequest::class;

    public function definition(): array
    {
        $type = fake()->randomElement(ProfileChangeType::cases());

        $payload = match ($type) {
            ProfileChangeType::Profile => ['phone' => fake()->phoneNumber(), 'biography' => fake()->sentence()],
            ProfileChangeType::Skill => ['proficiency_level' => fake()->numberBetween(3, 5)],
            ProfileChangeType::Language => ['speaking_level' => 'advanced', 'writing_level' => 'intermediate', 'reading_level' => 'advanced'],
            ProfileChangeType::Certification => ['certificate_number' => strtoupper(fake()->bothify('???-######'))],
            ProfileChangeType::Availability => ['start_date' => fake()->date(), 'end_date' => fake()->date(), 'availability_percentage' => 50],
        };

        return [
            'employee_id' => Employee::factory(),
            'type' => $type->value,
            'subject_id' => $type === ProfileChangeType::Profile || $type === ProfileChangeType::Availability ? null : fake()->numberBetween(1, 10),
            'payload' => $payload,
            'previous' => null,
            'status' => ProfileChangeStatus::Pending->value,
            'submitted_note' => null,
            'reviewer_id' => null,
            'reviewer_note' => null,
            'reviewed_at' => null,
        ];
    }

    public function ofType(ProfileChangeType $type): static
    {
        return $this->state(fn () => ['type' => $type->value]);
    }

    public function status(ProfileChangeStatus $status): static
    {
        return $this->state(fn () => ['status' => $status->value]);
    }
}
