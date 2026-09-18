<?php

namespace Database\Seeders;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Models\Certification;
use App\Models\Employee;
use App\Models\Language;
use App\Models\ProfileChangeRequest;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\Skill;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class HistorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@djezzy.test')->firstOrFail();
        $hrUser = User::where('email', 'hr@example.com')->first();
        $pmUser = User::where('email', 'projects@example.com')->first();
        $resourceUser = User::where('email', 'resources@example.com')->first();

        $reviewers = collect([$hrUser, $admin, $resourceUser, $pmUser])->filter();

        $this->seedEvaluations($admin, $pmUser);
        $this->seedRecommendationRuns($admin);
        $this->seedProfileChangeRequests($reviewers);
    }

    private function seedEvaluations(User $admin, ?User $pmUser): void
    {
        $evaluable = Project::query()
            ->whereIn('status', ['in_progress', 'assigned', 'completed', 'archived', 'cancelled'])
            ->get();

        if ($evaluable->isEmpty()) {
            return;
        }

        foreach (range(1, 90) as $i) {
            $project = $evaluable->random();
            $evaluator = fake()->boolean(70) ? $admin : ($pmUser ?? $admin);

            $rating = fake()->numberBetween(2, 5);

            ProjectEvaluation::create([
                'project_id' => $project->id,
                'employee_id' => $project->assignments()->inRandomOrder()->first()?->members()->inRandomOrder()->first()?->id,
                'evaluator_id' => $evaluator->id,
                'rating' => $rating,
                'communication_rating' => fake()->boolean(80) ? fake()->numberBetween(2, 5) : null,
                'delivery_rating' => fake()->boolean(80) ? fake()->numberBetween(2, 5) : null,
                'quality_rating' => fake()->boolean(80) ? fake()->numberBetween(2, 5) : null,
                'comments' => fake()->boolean(70) ? fake()->sentence(12) : null,
                'evaluated_at' => now()->subMonths(fake()->numberBetween(1, 8))->subDays(fake()->numberBetween(0, 20)),
            ]);
        }
    }

    private function seedRecommendationRuns(User $admin): void
    {
        $projects = Project::query()
            ->inRandomOrder()
            ->take(30)
            ->get();

        foreach ($projects as $project) {
            try {
                app(RecommendationService::class)->run($project, $admin);
            } catch (\Throwable $e) {
                // Non-fatal: keep seeding rich history even if one run is unexpected.
                report($e);
            }
        }
    }

    /** @param Collection<int, User> $reviewers */
    private function seedProfileChangeRequests(Collection $reviewers): void
    {
        $employees = Employee::active()->get();
        $skills = Skill::query()->get();
        $languages = Language::query()->get();
        $certifications = Certification::query()->get();

        $statuses = [
            ...array_fill(0, 52, ProfileChangeStatus::Approved),
            ...array_fill(0, 12, ProfileChangeStatus::Rejected),
            ...array_fill(0, 16, ProfileChangeStatus::Pending),
        ];

        foreach (range(0, count($statuses) - 1) as $i) {
            $employee = $employees->random();
            $status = $statuses[$i];
            $type = $this->randomChangeType();

            [$subjectId, $payload] = $this->payloadFor($type, $employee, $skills, $languages, $certifications);

            $createdAt = now()->subMonths(fake()->numberBetween(1, 9))->subDays(fake()->numberBetween(0, 20));

            $request = new ProfileChangeRequest([
                'employee_id' => $employee->id,
                'type' => $type,
                'subject_id' => $subjectId,
                'payload' => $payload,
                'status' => $status,
                'submitted_note' => fake()->boolean(70) ? fake()->sentence(8) : null,
            ]);

            $request->created_at = $createdAt;

            if ($status !== ProfileChangeStatus::Pending) {
                $reviewer = $reviewers->isEmpty() ? null : $reviewers->random();
                $request->reviewer_id = $reviewer?->id;
                $request->reviewed_at = $createdAt->copy()->addDays(fake()->numberBetween(1, 5));
                $request->reviewer_note = fake()->boolean(60) ? fake()->sentence(10) : null;
            }

            $request->save();
        }
    }

    private function randomChangeType(): ProfileChangeType
    {
        $pool = [
            ...array_fill(0, 8, ProfileChangeType::Profile),
            ...array_fill(0, 14, ProfileChangeType::Skill),
            ...array_fill(0, 6, ProfileChangeType::Language),
            ...array_fill(0, 8, ProfileChangeType::Certification),
            ...array_fill(0, 9, ProfileChangeType::Availability),
        ];

        return $pool[array_rand($pool)];
    }

    /**
     * @return array{0: int|null, 1: array<string, mixed>}
     */
    private function payloadFor(ProfileChangeType $type, Employee $employee, Collection $skills, Collection $languages, Collection $certifications): array
    {
        return match ($type) {
            ProfileChangeType::Profile => [null, $this->profilePayload()],
            ProfileChangeType::Skill => $this->skillPayload($employee, $skills),
            ProfileChangeType::Language => $this->languagePayload($languages),
            ProfileChangeType::Certification => $this->certificationPayload($certifications),
            ProfileChangeType::Availability => [null, $this->availabilityPayload()],
        };
    }

    /** @return array<string, mixed> */
    private function profilePayload(): array
    {
        return fake()->randomElement([
            ['phone' => '+213 '.fake()->numerify('5#########'), 'biography' => fake()->paragraph()],
            ['phone' => '+213 '.fake()->numerify('5#########')],
            ['biography' => fake()->paragraph()],
            ['birth_date' => fake()->dateTimeBetween('-45 years', '-22 years')->format('Y-m-d')],
        ]);
    }

    /** @return array{0: int, 1: array<string, mixed>} */
    private function skillPayload(Employee $employee, Collection $skills): array
    {
        $skill = $employee->skills()->inRandomOrder()->first() ?? $skills->random();

        return [
            $skill->id,
            [
                'proficiency_level' => fake()->numberBetween(3, 5),
                'years_experience' => fake()->randomFloat(1, 1, 10),
                'last_used_at' => now()->subDays(fake()->numberBetween(0, 200))->format('Y-m-d'),
                'notes' => fake()->optional()->sentence(6),
            ],
        ];
    }

    /** @return array{0: int, 1: array<string, mixed>} */
    private function languagePayload(Collection $languages): array
    {
        $language = $languages->random();

        return [
            $language->id,
            [
                'speaking_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'native']),
                'writing_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'native']),
                'reading_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'native']),
            ],
        ];
    }

    /** @return array{0: int, 1: array<string, mixed>} */
    private function certificationPayload(Collection $certifications): array
    {
        $certification = $certifications->random();
        $issuedAt = now()->subMonths(fake()->numberBetween(1, 36));

        return [
            $certification->id,
            [
                'certificate_number' => strtoupper((string) fake()->bothify('???-#######')),
                'issued_at' => $issuedAt->format('Y-m-d'),
                'expires_at' => fake()->boolean(70) ? $issuedAt->addMonths(fake()->numberBetween(12, 36))->format('Y-m-d') : null,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function availabilityPayload(): array
    {
        $start = now()->addMonths(fake()->numberBetween(0, 3));
        $percentage = fake()->randomElement([0, 0, 50, 75, 100]);

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $start->addMonths(fake()->numberBetween(1, 6))->format('Y-m-d'),
            'availability_percentage' => $percentage,
            'reason' => $percentage === 0 ? fake()->randomElement(['Medical leave', 'Sabbatical', 'Parental leave']) : null,
        ];
    }
}
