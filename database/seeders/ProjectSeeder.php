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
use App\Models\AssignmentStatusHistory;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWorkload;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectRequirement;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public const PROJECT_COUNT = 50;

    private const MANDATORY_SKILLS = EmployeeSeeder::BREADTH_SKILL_NAMES;

    private const MANDATORY_CERTS = EmployeeSeeder::BREADTH_CERT_NAMES;

    /**
     * @var array<int, ProjectStatus>
     */
    private array $statusByIndex = [];

    public function run(): void
    {
        $this->statusByIndex = $this->planStatuses();

        $categories = [
            'IT', 'Network', 'Finance', 'Marketing', 'HR', 'Operations',
            'Customer Service', 'Legal', 'R&D', 'Sales', 'Strategy',
            'Security', 'Data & Analytics', 'Customer Experience',
        ];

        foreach ($categories as $name) {
            ProjectCategory::firstOrCreate(['name' => $name]);
        }

        $admin = User::where('email', 'admin@djezzy.test')->firstOrFail();
        $breadthIds = EmployeeSeeder::breadthIds();

        $departments = Department::query()->get();
        $skillsBySlug = Skill::query()->get()->keyBy('slug');
        $certifications = Certification::query()->get();
        $languages = Language::query()->get();

        $pmCandidates = Employee::query()
            ->active()
            ->whereHas('position', fn ($q) => $q->where('code', 'POS-PM'))
            ->get();

        if ($pmCandidates->isEmpty()) {
            $pmCandidates = Employee::active()->get();
        }

        $projects = [];
        $codes = $this->projectCodes();

        foreach (range(1, self::PROJECT_COUNT) as $index) {
            [$name, $category, $priority] = $codes[$index];
            $status = $this->statusByIndex[$index];

            $project = Project::create([
                'project_code' => "PRJ-{$this->year()}-".sprintf('%03d', $index),
                'name' => $name,
                'description' => "{$name}. ".fake()->paragraph(2),
                'category_id' => ProjectCategory::where('name', $category)->value('id'),
                'requesting_department_id' => $departments->random()->id,
                'owning_department_id' => $departments->random()->id,
                'project_manager_id' => $pmCandidates->random()->id,
                'created_by' => $admin->id,
                'priority' => $priority,
                'status' => $status,
                'confidentiality_level' => fake()->randomElement(ConfidentialityLevel::cases()),
                'start_date' => $status === ProjectStatus::Draft ? null : now()->subMonths(fake()->numberBetween(0, 5))->toDateString(),
                'target_end_date' => now()->addMonths(fake()->numberBetween(2, 12))->toDateString(),
                'estimated_hours' => fake()->numberBetween(200, 12000),
                'estimated_budget' => fake()->numberBetween(200000, 9000000),
                'required_members_count' => fake()->numberBetween(2, 8),
                'assignment_mode' => $this->modeFor($index),
            ]);

            $projects[$index] = $project;

            $this->attachRequirements($project, $skillsBySlug, $certifications, $languages, $category);
        }

        $this->seedAssignments($projects, $admin, $breadthIds);
    }

    /** @return array<int, ProjectStatus> */
    private function planStatuses(): array
    {
        $sequence = [
            'in_progress' => 12,
            'assigned' => 8,
            'staffing' => 5,
            'under_review' => 6,
            'submitted' => 5,
            'completed' => 8,
            'cancelled' => 4,
            'archived' => 2,
        ];

        $statuses = [];
        foreach ($sequence as $value => $count) {
            for ($i = 0; $i < $count; $i++) {
                $statuses[] = ProjectStatus::from($value);
            }
        }

        $indexed = [];
        foreach (range(1, self::PROJECT_COUNT) as $index) {
            $indexed[$index] = $statuses[$index - 1] ?? ProjectStatus::Draft;
        }

        return $indexed;
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: ProjectPriority}>
     */
    private function projectCodes(): array
    {
        return [
            1 => ['5G Core Network Evolution', 'Network', ProjectPriority::Critical],
            2 => ['Mobile App Redesign & Relaunch', 'IT', ProjectPriority::High],
            3 => ['Prepaid Platform Upgrade', 'Network', ProjectPriority::Critical],
            4 => ['Data Center Migration to Cloud', 'IT', ProjectPriority::Critical],
            5 => ['National 4G Coverage Expansion', 'R&D', ProjectPriority::High],
            6 => ['AI-Driven Demand Forecasting', 'Data & Analytics', ProjectPriority::High],
            7 => ['Customer Self-Service Portal', 'Customer Service', ProjectPriority::High],
            8 => ['Retail Store Network Revamp', 'Operations', ProjectPriority::Medium],
            9 => ['Fraud Detection System', 'Security', ProjectPriority::Critical],
            10 => ['B2B Roaming Expansion for 2027', 'Sales', ProjectPriority::Medium],
            11 => ['RAN Energy Optimization', 'R&D', ProjectPriority::Medium],
            12 => ['Contact Center AI Copilot', 'Customer Service', ProjectPriority::High],
            13 => ['eKYC Onboarding Platform', 'IT', ProjectPriority::High],
            14 => ['Network Slicing Pilot', 'R&D', ProjectPriority::Medium],
            15 => ['Marketing Automation Suite', 'Marketing', ProjectPriority::Medium],
            16 => ['Business OSS/BSS Integration', 'IT', ProjectPriority::High],
            17 => ['Legal Contract Management System', 'Legal', ProjectPriority::Low],
            18 => ['Enterprise IoT Fleet Management', 'Strategy', ProjectPriority::High],
            19 => ['Postpaid Billing Modernization', 'Finance', ProjectPriority::High],
            20 => ['VoLTE/IMS Enablement', 'Network', ProjectPriority::High],
            21 => ['HR Recruitment Platform', 'HR', ProjectPriority::Medium],
            22 => ['Fiber-to-the-Home Expansion', 'Operations', ProjectPriority::High],
            23 => ['Cybersecurity Operations Center Upgrade', 'Security', ProjectPriority::Critical],
            24 => ['Real-Time Promo Engine Rollout', 'Marketing', ProjectPriority::Medium],
            25 => ['Cross-Channel Loyalty Program', 'Strategy', ProjectPriority::Medium],
            26 => ['Supplier e-Procurement', 'Finance', ProjectPriority::Low],
            27 => ['Workforce Scheduling System', 'HR', ProjectPriority::Medium],
            28 => ['OSS Inventory Harmonization', 'IT', ProjectPriority::High],
            29 => ['VoLTE Roaming Partners Rollout', 'Network', ProjectPriority::Medium],
            30 => ['Customer Experience (NPS) Program', 'Customer Experience', ProjectPriority::Medium],
            31 => ['Enterprise API Marketplace', 'Strategy', ProjectPriority::Medium],
            32 => ['SIM/eSIM Migration', 'R&D', ProjectPriority::Medium],
            33 => ['Real-Time Voucher Platform', 'Finance', ProjectPriority::High],
            34 => ['Partner Channel Management', 'Sales', ProjectPriority::Medium],
            35 => ['Environmental Sustainability Dashboard', 'Strategy', ProjectPriority::Low],
            36 => ['Emergency Call Prioritization', 'R&D', ProjectPriority::Critical],
            37 => ['RAN Vendor Modernization', 'R&D', ProjectPriority::High],
            38 => ['NOC Tool Consolidation', 'Network', ProjectPriority::Medium],
            39 => ['Microfinance API Partnership', 'Finance', ProjectPriority::Medium],
            40 => ['Enterprise BI Upgrade (Tableau)', 'Data & Analytics', ProjectPriority::Medium],
            41 => ['Contactless NFC Payment Service', 'Strategy', ProjectPriority::High],
            42 => ['IT Asset Management Overhaul', 'IT', ProjectPriority::Medium],
            43 => ['GDPR-Alignment Programme', 'Legal', ProjectPriority::High],
            44 => ['Talent Development Academy', 'HR', ProjectPriority::Low],
            45 => ['Cloud Cost Governance', 'IT', ProjectPriority::Medium],
            46 => ['5G Fixed Wireless Access Trials', 'R&D', ProjectPriority::Medium],
            47 => ['Physical Security with AI Cameras', 'Security', ProjectPriority::Low],
            48 => ['Smart Metering Connectivity (IoT)', 'Strategy', ProjectPriority::Medium],
            49 => ['Corporate Sustainability Report', 'Marketing', ProjectPriority::Low],
            50 => ['Sales Incentive Compensation Engine', 'Finance', ProjectPriority::Medium],
        ];
    }

    private function year(): string
    {
        return '2026';
    }

    private function modeFor(int $index): AssignmentMode
    {
        $modes = [
            1, 1, 2, 1, 2, 1, 1, 2, 1, 3,
            1, 1, 2, 1, 1, 2, 1, 1, 1, 1,
            2, 1, 3, 1, 2, 1, 1, 2, 1, 1,
            2, 1, 1, 2, 3, 1, 1, 2, 1, 2,
            1, 1, 2, 1, 1, 2, 1, 1, 3, 1,
        ];

        return match ($modes[$index - 1]) {
            2 => AssignmentMode::Team,
            3 => AssignmentMode::Department,
            default => AssignmentMode::MultipleEmployees,
        };
    }

    private function attachRequirements(Project $project, Collection $skillsBySlug, Collection $certifications, Collection $languages, string $category): void
    {
        $skillNames = $this->categorySkillPool($category);

        $chosenSkillNames = fake()->randomElements($skillNames, min(fake()->numberBetween(2, 5), count($skillNames)));

        $mandatorySkillName = self::MANDATORY_SKILLS[array_rand(self::MANDATORY_SKILLS)];
        if (! in_array($mandatorySkillName, $chosenSkillNames, true)) {
            $chosenSkillNames[] = $mandatorySkillName;
        }

        foreach ($chosenSkillNames as $skillName) {
            $skill = $skillsBySlug->get(Str::slug($skillName));
            if ($skill === null) {
                continue;
            }
            $project->requiredSkills()->attach($skill->id, [
                'minimum_proficiency' => fake()->numberBetween(3, 4),
                'minimum_years_experience' => fake()->randomFloat(1, 2, 6),
                'is_mandatory' => $skillName === $mandatorySkillName,
                'weight' => fake()->randomFloat(2, 1, 5),
            ]);
        }

        $mandatoryCerts = fake()->boolean(45)
            ? fake()->randomElements(self::MANDATORY_CERTS, fake()->numberBetween(1, 2))
            : [];
        $extraCerts = $certifications
            ->whereNotIn('name', $mandatoryCerts)
            ->shuffle()
            ->take(fake()->numberBetween(0, 2))
            ->pluck('name')
            ->all();

        foreach (array_merge($mandatoryCerts, $extraCerts) as $certName) {
            $cert = $certifications->firstWhere('name', $certName);
            if ($cert === null) {
                continue;
            }
            $project->requiredCertifications()->attach($cert->id, [
                'is_mandatory' => in_array($certName, $mandatoryCerts, true),
            ]);
        }

        foreach (fake()->randomElements(['ar', 'fr', 'en'], fake()->numberBetween(1, 2)) as $languageCode) {
            $language = $languages->firstWhere('code', $languageCode);
            if ($language === null) {
                continue;
            }
            $project->requiredLanguages()->attach($language->id, [
                'minimum_level' => fake()->randomElement(['intermediate', 'advanced']),
                'is_mandatory' => false,
            ]);
        }

        $project->requirements()->saveMany([
            new ProjectRequirement([
                'title' => 'Deliver within agreed timeline',
                'description' => 'Milestones must be met on schedule with weekly reporting.',
                'requirement_type' => RequirementType::Availability,
                'is_mandatory' => fake()->boolean(70),
                'weight' => 3,
            ]),
            new ProjectRequirement([
                'title' => 'Domain experience in operator context',
                'description' => 'Candidates should bring relevant telecom domain experience.',
                'requirement_type' => RequirementType::Experience,
                'is_mandatory' => false,
                'weight' => 2,
            ]),
            new ProjectRequirement([
                'title' => 'Stakeholder reporting and governance',
                'description' => 'Regular status reporting to the steering committee.',
                'requirement_type' => RequirementType::Position,
                'is_mandatory' => false,
                'weight' => 1,
            ]),
        ]);
    }

    /** @return array<int, string> */
    private function categorySkillPool(string $category): array
    {
        $pools = [
            'IT' => ['PHP', 'Laravel', 'Python', 'Java', 'JavaScript', 'TypeScript', 'React', 'Node.js', 'SQL', 'PostgreSQL', 'Redis', 'Docker', 'Kubernetes', 'CI/CD', 'Cloud Architecture', 'Agile Methodologies', 'Technical Writing', 'Linux Administration', 'Terraform'],
            'Network' => ['TCP/IP', 'Routing & Switching', 'MPLS', '4G/5G Core', 'Radio Access Networks', 'Cisco IOS', 'VoIP', 'SDN/NFV', 'Optical Transport (DWDM)', 'Network Monitoring (NOC)', 'SS7/Diameter', 'Fiber Optics'],
            'Security' => ['Information Security', 'Penetration Testing', 'SOC Analysis', 'IAM', 'Incident Response', 'Security Auditing', 'Firewalls', 'TCP/IP', 'Cloud Architecture'],
            'Finance' => ['Financial Analysis', 'Budgeting', 'SQL', 'Python', 'Business Intelligence', 'Excel', 'Power BI', 'Risk Management', 'Statistics'],
            'Marketing' => ['Digital Marketing', 'Content Creation', 'SEO/SEM', 'Brand Strategy', 'Campaign Management', 'Market Research', 'Statistics', 'UI/UX Design', 'Excel'],
            'HR' => ['Workforce Planning', 'Change Management', 'Project Management', 'Stakeholder Management', 'Coaching & Mentoring', 'Technical Writing'],
            'Operations' => ['Retail Operations', 'Customer Relationship Management', 'Negotiation', 'B2B Sales', 'Workforce Planning', 'Excel'],
            'Customer Service' => ['Customer Experience Management', 'Customer Relationship Management', 'Call Center Technology (CTI)', 'Negotiation', 'VoIP'],
            'Legal' => ['Risk Management', 'Negotiation', 'Technical Writing', 'Business Process Modeling'],
            'R&D' => ['Python', 'Machine Learning', 'Statistics', 'Cloud Architecture', 'Data Modeling', '4G/5G Core', 'Radio Access Networks', 'TCP/IP'],
            'Sales' => ['B2B Sales', 'Negotiation', 'Customer Relationship Management', 'Market Research', 'Financial Analysis'],
            'Strategy' => ['Market Research', 'Financial Analysis', 'Statistics', 'Risk Management', 'Stakeholder Management', 'Business Process Modeling', 'Data Modeling'],
            'Data & Analytics' => ['Python', 'SQL', 'PostgreSQL', 'Data Modeling', 'ETL', 'Business Intelligence', 'Power BI', 'Tableau', 'Machine Learning', 'Statistics', 'Big Data (Hadoop)'],
            'Customer Experience' => ['Customer Experience Management', 'Market Research', 'Statistics', 'Data Modeling', 'Business Intelligence', 'Digital Marketing'],
        ];

        return $pools[$category] ?? $pools['IT'];
    }

    /**
     * @param  array<int, Project>  $projects
     * @param  array<int, int>  $breadthIds
     */
    private function seedAssignments(array $projects, User $admin, array $breadthIds): void
    {
        $nonBreadthEmployees = Employee::active()->whereNotIn('id', $breadthIds)->get();

        $breadthTeamIds = Team::whereHas('employees', fn ($q) => $q->whereIn('id', $breadthIds))->pluck('id')->all();
        $nonBreadthTeams = Team::whereNotIn('id', $breadthTeamIds)->get();

        $statuses = $this->statusByIndex;

        foreach ($projects as $index => $project) {
            $status = $statuses[$index];
            $mode = $project->assignment_mode;

            $current = $this->currentAssignmentFor($status);

            if ($current !== null) {
                $this->makeAssignment($project, $admin, $current, $mode, $nonBreadthTeams, $nonBreadthEmployees, true);
            }

            if ($this->wantsHistoricalAssignments($index, $status)) {
                $this->makeAssignment($project, $admin, AssignmentStatus::Completed, $mode, $nonBreadthTeams, $nonBreadthEmployees, false);
            }

            if (($index + 1) % 6 === 0) {
                $this->makeAssignment($project, $admin, fake()->randomElement([AssignmentStatus::Rejected, AssignmentStatus::Cancelled]), $mode, $nonBreadthTeams, $nonBreadthEmployees, false);
            }
        }

        $this->syncCurrentWorkloads();
    }

    private function currentAssignmentFor(ProjectStatus $status): ?AssignmentStatus
    {
        return match ($status) {
            ProjectStatus::InProgress, ProjectStatus::Assigned => AssignmentStatus::Active,
            ProjectStatus::Staffing => AssignmentStatus::Approved,
            ProjectStatus::UnderReview, ProjectStatus::Submitted => AssignmentStatus::Pending,
            ProjectStatus::Completed, ProjectStatus::Archived => AssignmentStatus::Completed,
            ProjectStatus::Cancelled => AssignmentStatus::Cancelled,
            default => null,
        };
    }

    private function wantsHistoricalAssignments(int $index, ProjectStatus $status): bool
    {
        if (in_array($status, [ProjectStatus::Completed, ProjectStatus::Archived], true)) {
            return true;
        }

        return $index % 2 === 1;
    }

    private function makeAssignment(
        Project $project,
        User $admin,
        AssignmentStatus $status,
        AssignmentMode $mode,
        Collection $nonBreadthTeams,
        Collection $nonBreadthEmployees,
        bool $whetherCurrent,
    ): void {
        $now = now();

        $needsApproval = in_array($status, [AssignmentStatus::Pending, AssignmentStatus::Rejected, AssignmentStatus::Cancelled], true);

        $start = $whetherCurrent
            ? $now->subDays(fake()->numberBetween(1, 10))
            : $now->subMonths(fake()->numberBetween(6, 14));
        $end = $whetherCurrent
            ? $now->addMonths(fake()->numberBetween(2, 8))
            : $now->subMonths(fake()->numberBetween(1, 4));

        $assignment = Assignment::create([
            'project_id' => $project->id,
            'assigned_by' => $admin->id,
            'approved_by' => $needsApproval ? null : $admin->id,
            'assignment_type' => $this->assignmentTypeFor($mode),
            'status' => $status,
            'assigned_at' => $start->copy(),
            'approved_at' => $needsApproval ? null : $now->subDays(fake()->numberBetween(1, 25)),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'notes' => in_array($status, [AssignmentStatus::Rejected, AssignmentStatus::Cancelled], true)
                ? fake()->randomElement(['Capacity changed at the last minute.', 'Scope redefined by the sponsor.', 'Member availability dropped.'])
                : fake()->optional()->sentence(),
        ]);

        $this->attachTeamsAndMembers($assignment, $mode, $status, $nonBreadthTeams, $nonBreadthEmployees, $whetherCurrent);

        $this->recordStatusHistory($assignment, $admin, $status, $start);
    }

    private function assignmentTypeFor(AssignmentMode $mode): AssignmentType
    {
        return match ($mode) {
            AssignmentMode::Team => AssignmentType::Team,
            AssignmentMode::Department => AssignmentType::Department,
            default => AssignmentType::Employee,
        };
    }

    private function attachTeamsAndMembers(
        Assignment $assignment,
        AssignmentMode $mode,
        AssignmentStatus $status,
        Collection $nonBreadthTeams,
        Collection $nonBreadthEmployees,
        bool $whetherCurrent,
    ): void {
        if ($mode === AssignmentMode::Team) {
            $teams = $nonBreadthTeams->shuffle()->take(fake()->numberBetween(1, 2));

            foreach ($teams as $team) {
                $assignment->teams()->attach($team->id, [
                    'responsibility' => fake()->randomElement(['Delivery team', 'Implementation', 'Support squad']),
                    'allocation_percentage' => fake()->randomElement([50, 75, 100]),
                ]);
            }

            foreach ($teams as $team) {
                foreach ($team->employees->take(6) as $member) {
                    $this->attachMember($assignment, $member, $status, $whetherCurrent);
                }
            }

            return;
        }

        $count = $mode === AssignmentMode::Department ? fake()->numberBetween(4, 8) : fake()->numberBetween(2, 5);

        foreach ($nonBreadthEmployees->shuffle()->take($count) as $member) {
            $this->attachMember($assignment, $member, $status, $whetherCurrent);
        }
    }

    private function attachMember(Assignment $assignment, Employee $member, AssignmentStatus $status, bool $whetherCurrent): void
    {
        $joinedAt = $member->hire_date !== null && $member->hire_date->toDateString() > $assignment->start_date
            ? $member->hire_date->toDateString()
            : $assignment->start_date;

        $leftAt = in_array($status, [AssignmentStatus::Completed, AssignmentStatus::Cancelled, AssignmentStatus::Rejected], true)
            ? $assignment->end_date
            : null;

        $assignment->members()->attach($member->id, [
            'responsibility' => fake()->randomElement(['Lead', 'Developer', 'Analyst', 'Support', 'QA', 'Coordinator']),
            'allocation_percentage' => fake()->randomElement([25, 50, 75, 100]),
            'allocated_hours' => fake()->numberBetween(40, 480),
            'joined_at' => $joinedAt,
            'left_at' => $leftAt,
        ]);
    }

    private function recordStatusHistory(Assignment $assignment, User $admin, AssignmentStatus $finalStatus, $startedAt): void
    {
        $transitions = [AssignmentStatus::Pending];

        if (in_array($finalStatus, [AssignmentStatus::Approved, AssignmentStatus::Active, AssignmentStatus::Completed], true)) {
            $transitions[] = AssignmentStatus::Approved;
        }
        if (in_array($finalStatus, [AssignmentStatus::Active, AssignmentStatus::Completed], true)) {
            $transitions[] = AssignmentStatus::Active;
        }
        if (in_array($finalStatus, [AssignmentStatus::Completed], true)) {
            $transitions[] = AssignmentStatus::Completed;
        }
        if ($finalStatus === AssignmentStatus::Rejected) {
            $transitions[] = AssignmentStatus::Rejected;
        }
        if ($finalStatus === AssignmentStatus::Cancelled) {
            $transitions[] = AssignmentStatus::Cancelled;
        }

        $offset = 0;
        foreach ($transitions as $i => $newStatus) {
            $offset += fake()->numberBetween(1, 4);

            $history = new AssignmentStatusHistory([
                'assignment_id' => $assignment->id,
                'old_status' => $i === 0 ? null : $transitions[$i - 1],
                'new_status' => $newStatus,
                'changed_by' => $admin->id,
                'reason' => match ($newStatus) {
                    AssignmentStatus::Pending => 'Assignment created',
                    AssignmentStatus::Approved => 'Resource manager approval',
                    AssignmentStatus::Active => 'Work started',
                    AssignmentStatus::Completed => 'Delivered and closed',
                    AssignmentStatus::Cancelled => 'Assignment cancelled',
                    AssignmentStatus::Rejected => 'Assignment rejected',
                },
            ]);

            $history->created_at = $startedAt->copy()->addDays($offset);
            $history->save();
        }
    }

    private function syncCurrentWorkloads(): void
    {
        $activeMemberships = DB::table('assignment_members as am')
            ->join('assignments as a', 'a.id', '=', 'am.assignment_id')
            ->whereIn('a.status', [AssignmentStatus::Active->value, AssignmentStatus::Approved->value])
            ->whereNull('am.left_at')
            ->select('am.employee_id', 'am.allocation_percentage')
            ->get();

        $totals = [];
        foreach ($activeMemberships as $row) {
            $totals[$row->employee_id] = ($totals[$row->employee_id] ?? 0) + (float) $row->allocation_percentage;
        }

        foreach ($totals as $employeeId => $allocation) {
            $workload = EmployeeWorkload::query()
                ->where('employee_id', $employeeId)
                ->whereDate('period_start', '<=', now()->toDateString())
                ->whereDate('period_end', '>=', now()->toDateString())
                ->latest('id')
                ->first();

            if ($workload !== null) {
                $workload->update(['allocated_percentage' => min(round($allocation, 2), 90)]);
            }
        }
    }
}
