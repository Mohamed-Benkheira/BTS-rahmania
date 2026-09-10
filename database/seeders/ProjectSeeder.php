<?php

namespace Database\Seeders;

use App\Enums\AssignmentMode;
use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\RequirementType;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\AssignmentStatusHistory;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectRequirement;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'IT', 'Network', 'Finance', 'Marketing', 'HR', 'Operations', 'Customer Service', 'Legal', 'R&D', 'Sales',
        ];

        foreach ($categories as $name) {
            ProjectCategory::firstOrCreate(['name' => $name]);
        }

        $user = User::firstOrCreate(
            ['email' => 'admin@djezzy.test'],
            ['name' => 'Djezzy Administrator', 'password' => Hash::make('password')]
        );

        $projects = [
            ['Mobile App Redesign', 'IT', ProjectPriority::High, ProjectStatus::InProgress, 'PRJ-2026-001'],
            ['5G Core Network Rollout', 'Network', ProjectPriority::Critical, ProjectStatus::Staffing, 'PRJ-2026-002'],
            ['Customer Billing Integration', 'Finance', ProjectPriority::High, ProjectStatus::UnderReview, 'PRJ-2026-003'],
            ['Social Media Campaign Q3', 'Marketing', ProjectPriority::Medium, ProjectStatus::Submitted, 'PRJ-2026-004'],
            ['Employee Portal Revamp', 'HR', ProjectPriority::Medium, ProjectStatus::Draft, 'PRJ-2026-005'],
            ['Data Center Migration', 'IT', ProjectPriority::Critical, ProjectStatus::InProgress, 'PRJ-2026-006'],
            ['Chatbot for Support', 'Customer Service', ProjectPriority::High, ProjectStatus::Staffing, 'PRJ-2026-007'],
            ['Retail Store Expansion', 'Operations', ProjectPriority::Medium, ProjectStatus::UnderReview, 'PRJ-2026-008'],
            ['Contract Management System', 'Legal', ProjectPriority::Low, ProjectStatus::Draft, 'PRJ-2026-009'],
            ['AI Demand Forecasting', 'R&D', ProjectPriority::High, ProjectStatus::Submitted, 'PRJ-2026-010'],
        ];

        $created = [];
        foreach ($projects as [$name, $category, $priority, $status, $code]) {
            $created[] = Project::create([
                'project_code' => $code,
                'name' => $name,
                'description' => fake()->paragraph(3),
                'category_id' => ProjectCategory::where('name', $category)->value('id'),
                'requesting_department_id' => Department::inRandomOrder()->value('id'),
                'owning_department_id' => Department::inRandomOrder()->value('id'),
                'project_manager_id' => Employee::inRandomOrder()->value('id'),
                'created_by' => $user->id,
                'priority' => $priority,
                'status' => $status,
                'confidentiality_level' => fake()->randomElement(ConfidentialityLevel::cases()),
                'start_date' => now()->subDays(fake()->numberBetween(0, 60))->format('Y-m-d'),
                'target_end_date' => now()->addMonths(fake()->numberBetween(2, 10))->format('Y-m-d'),
                'estimated_hours' => fake()->numberBetween(200, 8000),
                'estimated_budget' => fake()->numberBetween(100000, 5000000),
                'required_members_count' => fake()->numberBetween(2, 10),
                'assignment_mode' => fake()->randomElement([
                    AssignmentMode::MultipleEmployees,
                    AssignmentMode::Team,
                ]),
            ]);
        }

        $skills = Skill::pluck('id')->all();
        $coreCertificationSlugs = [
            'cisco-ccna', 'cisco-ccnp', 'aws-solutions-architect', 'azure-administrator',
            'certified-information-systems-security-professional', 'certified-scrum-master',
            'pmp', 'oracle-certified-professional', 'google-cloud-professional', 'red-hat-certified-engineer',
        ];
        $certifications = Certification::whereIn('slug', $coreCertificationSlugs)->pluck('id')->all();
        $languages = Language::pluck('id')->all();

        foreach ($created as $project) {
            foreach (fake()->randomElements($skills, fake()->numberBetween(2, 4)) as $skillId) {
                $project->requiredSkills()->attach($skillId, [
                    'minimum_proficiency' => fake()->numberBetween(3, 4),
                    'minimum_years_experience' => fake()->randomFloat(1, 2, 6),
                    'is_mandatory' => fake()->boolean(60),
                    'weight' => fake()->randomFloat(2, 0.5, 5),
                ]);
            }

            foreach (fake()->randomElements($certifications, fake()->numberBetween(0, 2)) as $certificationId) {
                $project->requiredCertifications()->attach($certificationId, [
                    'is_mandatory' => fake()->boolean(50),
                ]);
            }

            foreach (fake()->randomElements($languages, fake()->numberBetween(0, 2)) as $languageId) {
                $project->requiredLanguages()->attach($languageId, [
                    'minimum_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
                    'is_mandatory' => fake()->boolean(50),
                ]);
            }

            $project->requirements()->saveMany([
                new ProjectRequirement([
                    'title' => 'Deliver within agreed timeline',
                    'description' => 'Project milestones must be met on schedule.',
                    'requirement_type' => RequirementType::Availability,
                    'is_mandatory' => true,
                    'weight' => 3,
                ]),
                new ProjectRequirement([
                    'title' => 'Regular stakeholder reporting',
                    'description' => 'Weekly status reports to the steering committee.',
                    'requirement_type' => RequirementType::Position,
                    'is_mandatory' => false,
                    'weight' => 1,
                ]),
            ]);
        }

        $this->seedManualAssignments($created);
    }

    /** @param array<int, Project> $projects */
    private function seedManualAssignments(array $projects): void
    {
        $assignable = collect($projects)->take(5);
        $employees = Employee::where('employment_status', 'active')->get();
        $teams = Team::pluck('id')->all();
        $user = User::where('email', 'admin@djezzy.test')->firstOrFail();

        $statuses = [
            AssignmentStatus::Active,
            AssignmentStatus::Approved,
            AssignmentStatus::Pending,
            AssignmentStatus::Completed,
            AssignmentStatus::Active,
        ];

        foreach ($assignable as $index => $project) {
            $status = $statuses[$index];

            $assignment = Assignment::create([
                'project_id' => $project->id,
                'assigned_by' => $user->id,
                'approved_by' => $status !== AssignmentStatus::Pending ? $user->id : null,
                'assignment_type' => $index % 2 === 0 ? AssignmentType::Employee : AssignmentType::Mixed,
                'status' => $status,
                'assigned_at' => now()->subDays(fake()->numberBetween(1, 30)),
                'approved_at' => $status === AssignmentStatus::Pending ? null : now()->subDays(fake()->numberBetween(1, 25)),
                'start_date' => now()->subDays(fake()->numberBetween(1, 10))->format('Y-m-d'),
                'end_date' => now()->addMonths(fake()->numberBetween(2, 6))->format('Y-m-d'),
                'notes' => fake()->optional()->sentence(),
            ]);

            $team = $employees->random(2);
            foreach ($team as $member) {
                AssignmentMember::create([
                    'assignment_id' => $assignment->id,
                    'employee_id' => $member->id,
                    'responsibility' => fake()->randomElement(['Lead', 'Developer', 'Analyst', 'Support']),
                    'allocation_percentage' => fake()->randomElement([50, 75, 100]),
                    'allocated_hours' => fake()->numberBetween(40, 240),
                    'joined_at' => $assignment->start_date,
                ]);
            }

            if ($index % 2 === 1) {
                $assignment->teams()->attach($teams[$index % count($teams)], [
                    'responsibility' => 'Delivery team',
                    'allocation_percentage' => 100,
                ]);
            }

            AssignmentStatusHistory::create([
                'assignment_id' => $assignment->id,
                'old_status' => null,
                'new_status' => AssignmentStatus::Pending,
                'changed_by' => $user->id,
                'reason' => 'Created',
            ]);

            if ($status !== AssignmentStatus::Pending) {
                AssignmentStatusHistory::create([
                    'assignment_id' => $assignment->id,
                    'old_status' => AssignmentStatus::Pending,
                    'new_status' => $status,
                    'changed_by' => $user->id,
                    'reason' => $status === AssignmentStatus::Completed ? 'Work finished' : 'Approved and started',
                ]);
            }
        }
    }
}
