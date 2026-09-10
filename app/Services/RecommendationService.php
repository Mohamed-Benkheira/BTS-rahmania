<?php

namespace App\Services;

use App\Enums\AssignmentMode;
use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Recommendation;
use App\Models\RecommendationRun;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    private AuditService $audit;

    /** @var array<string, float> */
    private array $weights = [
        'skills' => 0.40,
        'certifications' => 0.20,
        'languages' => 0.15,
        'availability' => 0.15,
        'workload' => 0.10,
    ];

    /** @var array<string, string> */
    private const LEVEL_ORDER = [
        'beginner' => 1,
        'elementary' => 2,
        'intermediate' => 3,
        'advanced' => 4,
        'native' => 5,
        'fluent' => 5,
    ];

    private const ALGORITHM_VERSION = '1.1.0';

    /** Neutral score used when a candidate has no current availability record. */
    private const DEFAULT_AVAILABILITY_SCORE = 0.5;

    public function __construct(?AuditService $audit = null)
    {
        $this->audit = $audit ?? app(AuditService::class);
    }

    /** @param array<string, float> $weights */
    public function weights(array $weights): static
    {
        $this->weights = array_merge($this->weights, $weights);

        return $this;
    }

    public function run(Project $project, User $executedBy): RecommendationRun
    {
        $mode = $project->assignment_mode;

        $eligibleCount = match ($mode) {
            AssignmentMode::Team => $this->findEligibleTeams($project)->count(),
            AssignmentMode::Department => $this->findEligibleDepartments($project)->count(),
            default => $this->findEligibleEmployees($project)->count(),
        };

        $scored = match ($mode) {
            AssignmentMode::Team => $this->scoreTeams($project),
            AssignmentMode::Department => $this->scoreDepartments($project),
            default => $this->scoreEmployees($project),
        };

        $blockers = $scored->isEmpty() ? $this->mandatoryBlockers($project) : null;

        $run = RecommendationRun::create([
            'project_id' => $project->id,
            'executed_by' => $executedBy->id,
            'algorithm_version' => self::ALGORITHM_VERSION,
            'criteria_snapshot' => $this->buildCriteriaSnapshot($project),
            'blockers' => $blockers,
            'executed_at' => now(),
        ]);

        $rank = 0;
        foreach ($scored as $item) {
            $rank++;
            Recommendation::create([
                'recommendation_run_id' => $run->id,
                'employee_id' => $item['employee_id'],
                'team_id' => $item['team_id'],
                'department_id' => $item['department_id'],
                'total_score' => round($item['total_score'], 4),
                'rank' => $rank,
                'eligibility_status' => 'eligible',
                'explanation' => $item['explanation'],
                'status' => 'pending',
            ]);
        }

        $this->audit->log(
            'recommendation.run',
            $run,
            null,
            [
                'project_id' => $project->id,
                'assignment_mode' => $mode->value,
                'candidates_count' => $scored->count(),
                'eligible_count' => $eligibleCount,
                'excluded_by_mandatory' => max(0, $eligibleCount - $scored->count()),
                'blockers' => $blockers,
                'rankings' => $rank,
            ],
            $executedBy,
        );

        return $run->load(['recommendations.employee', 'recommendations.team', 'recommendations.department']);
    }

    // ── Employee scoring ──────────────────────────────────────────────

    /** @return \Illuminate\Support\Collection<int, array{employee_id: int, team_id: null, department_id: null, total_score: float, explanation: array}> */
    private function scoreEmployees(Project $project): \Illuminate\Support\Collection
    {
        $candidates = $this->findEligibleEmployees($project);

        return $candidates->map(fn (Employee $e) => $this->scoreEmployee($e, $project))
            ->filter()
            ->sortByDesc('total_score')
            ->values();
    }

    /** @return Collection<int, Employee> */
    private function findEligibleEmployees(Project $project): Collection
    {
        $assignedEmployeeIds = $project->assignments()
            ->whereIn('status', ['approved', 'active'])
            ->with('members')
            ->get()
            ->flatMap(fn ($a) => $a->members)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return Employee::query()
            ->where('employment_status', EmploymentStatus::Active->value)
            ->whereNotIn('id', $assignedEmployeeIds)
            ->with(['skills', 'certifications', 'languages', 'availabilities', 'workloads'])
            ->get();
    }

    /** @return array{employee_id: int, team_id: null, department_id: null, total_score: float, explanation: array<string, mixed>}|null */
    private function scoreEmployee(Employee $employee, Project $project): ?array
    {
        if ($this->mandatoryRequirementReason($project, $employee->skills, $employee->certifications, $employee->languages) !== null) {
            return null;
        }

        $components = $this->scoreCapabilities(
            $employee->skills,
            $employee->certifications,
            $employee->languages,
            $employee->availabilities,
            $employee->workloads,
            $project,
        );

        return [
            'employee_id' => $employee->id,
            'team_id' => null,
            'department_id' => null,
            'total_score' => $components['total_score'],
            'explanation' => $components['explanation'],
        ];
    }

    // ── Team scoring ──────────────────────────────────────────────────

    /** @return \Illuminate\Support\Collection<int, array{employee_id: null, team_id: int, department_id: null, total_score: float, explanation: array}> */
    private function scoreTeams(Project $project): \Illuminate\Support\Collection
    {
        $candidates = $this->findEligibleTeams($project);

        return $candidates->map(fn (Team $t) => $this->scoreTeam($t, $project))
            ->filter()
            ->sortByDesc('total_score')
            ->values();
    }

    /** @return Collection<int, Team> */
    private function findEligibleTeams(Project $project): Collection
    {
        $assignedTeamIds = $project->assignments()
            ->whereIn('status', ['approved', 'active'])
            ->with('teams')
            ->get()
            ->flatMap(fn ($a) => $a->teams)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return Team::query()
            ->whereHas('employees', fn ($q) => $q->where('employment_status', EmploymentStatus::Active->value))
            ->with(['employees.skills', 'employees.certifications', 'employees.languages', 'employees.availabilities', 'employees.workloads'])
            ->when($assignedTeamIds, fn ($q) => $q->whereNotIn('id', $assignedTeamIds))
            ->get();
    }

    /** @return array{employee_id: null, team_id: int, department_id: null, total_score: float, explanation: array<string, mixed>}|null */
    private function scoreTeam(Team $team, Project $project): ?array
    {
        $activeEmployees = $team->employees
            ->filter(fn (Employee $e) => $e->employment_status === EmploymentStatus::Active);

        $allSkills = $activeEmployees->flatMap(fn ($e) => $e->skills)->unique('id');
        $allCerts = $activeEmployees
            ->flatMap(fn ($e) => $e->certifications->filter(fn ($c) => $c->pivot->isCurrentlyValid()))
            ->unique('id');
        $allLangs = $activeEmployees->flatMap(fn ($e) => $e->languages)->unique('id');
        $allAvailabilities = $activeEmployees->flatMap(fn ($e) => $e->availabilities);
        $allWorkloads = $activeEmployees->flatMap(fn ($e) => $e->workloads);

        if ($this->mandatoryRequirementReason($project, $allSkills, $allCerts, $allLangs) !== null) {
            return null;
        }

        $components = $this->scoreCapabilities($allSkills, $allCerts, $allLangs, $allAvailabilities, $allWorkloads, $project);

        $components['explanation']['team_size'] = $activeEmployees->count();

        return [
            'employee_id' => null,
            'team_id' => $team->id,
            'department_id' => null,
            'total_score' => $components['total_score'],
            'explanation' => $components['explanation'],
        ];
    }

    // ── Department scoring ────────────────────────────────────────────

    /** @return \Illuminate\Support\Collection<int, array{employee_id: null, team_id: null, department_id: int, total_score: float, explanation: array}> */
    private function scoreDepartments(Project $project): \Illuminate\Support\Collection
    {
        $candidates = $this->findEligibleDepartments($project);

        return $candidates->map(fn (Department $d) => $this->scoreDepartment($d, $project))
            ->filter()
            ->sortByDesc('total_score')
            ->values();
    }

    /** @return Collection<int, Department> */
    private function findEligibleDepartments(Project $project): Collection
    {
        return Department::query()
            ->whereHas('employees', fn ($q) => $q->where('employment_status', EmploymentStatus::Active->value))
            ->with(['employees.skills', 'employees.certifications', 'employees.languages', 'employees.availabilities', 'employees.workloads'])
            ->get();
    }

    /** @return array{employee_id: null, team_id: null, department_id: int, total_score: float, explanation: array<string, mixed>}|null */
    private function scoreDepartment(Department $department, Project $project): ?array
    {
        $activeEmployees = $department->employees
            ->filter(fn (Employee $e) => $e->employment_status === EmploymentStatus::Active);

        $allSkills = $activeEmployees->flatMap(fn ($e) => $e->skills)->unique('id');
        $allCerts = $activeEmployees
            ->flatMap(fn ($e) => $e->certifications->filter(fn ($c) => $c->pivot->isCurrentlyValid()))
            ->unique('id');
        $allLangs = $activeEmployees->flatMap(fn ($e) => $e->languages)->unique('id');
        $allAvailabilities = $activeEmployees->flatMap(fn ($e) => $e->availabilities);
        $allWorkloads = $activeEmployees->flatMap(fn ($e) => $e->workloads);

        if ($this->mandatoryRequirementReason($project, $allSkills, $allCerts, $allLangs) !== null) {
            return null;
        }

        $components = $this->scoreCapabilities($allSkills, $allCerts, $allLangs, $allAvailabilities, $allWorkloads, $project);

        $components['explanation']['department_size'] = $activeEmployees->count();

        return [
            'employee_id' => null,
            'team_id' => null,
            'department_id' => $department->id,
            'total_score' => $components['total_score'],
            'explanation' => $components['explanation'],
        ];
    }

    // ── Shared scoring engine ─────────────────────────────────────────

    /**
     * @param  Collection  $skills
     * @param  Collection  $certs
     * @param  Collection  $langs
     * @param  Collection  $availabilities
     * @param  Collection  $workloads
     * @return array{total_score: float, explanation: array<string, mixed>}
     */
    private function scoreCapabilities(
        $skills,
        $certs,
        $langs,
        $availabilities,
        $workloads,
        Project $project,
    ): array {
        $activeWeights = $this->weights;

        $skillResult = $this->scoreSkillsFromCollection($skills, $project);
        $certResult = $this->scoreCertificationsFromCollection($certs, $project);
        $langResult = $this->scoreLanguagesFromCollection($langs, $project);
        $availabilityScore = $this->scoreAvailabilityFromCollection($availabilities);
        $workloadScore = $this->scoreWorkloadFromCollection($workloads);

        $components = [
            'skills' => $skillResult,
            'certifications' => $certResult,
            'languages' => $langResult,
            'availability' => $availabilityScore,
            'workload' => $workloadScore,
        ];

        $hasSkills = $project->requiredSkills()->count() > 0;
        $hasCerts = $project->requiredCertifications()->count() > 0;
        $hasLangs = $project->requiredLanguages()->count() > 0;

        if (! $hasSkills) {
            unset($activeWeights['skills']);
        }
        if (! $hasCerts) {
            unset($activeWeights['certifications']);
        }
        if (! $hasLangs) {
            unset($activeWeights['languages']);
        }

        $weightSum = array_sum($activeWeights);

        if ($weightSum === 0.0) {
            $totalScore = 0.0;
        } else {
            $totalScore = (
                ($skillResult['score'] * ($activeWeights['skills'] ?? 0))
                + ($certResult['score'] * ($activeWeights['certifications'] ?? 0))
                + ($langResult['score'] * ($activeWeights['languages'] ?? 0))
                + ($availabilityScore * ($activeWeights['availability'] ?? 0))
                + ($workloadScore * ($activeWeights['workload'] ?? 0))
            ) / $weightSum;
        }

        return [
            'total_score' => $totalScore,
            'explanation' => $components,
        ];
    }

    /**
     * Returns a human-readable reason when a candidate fails a mandatory
     * requirement, or null when the candidate remains eligible.
     *
     * @param  Collection  $skills
     * @param  Collection  $certs
     * @param  Collection  $langs
     */
    private function mandatoryRequirementReason(Project $project, $skills, $certs, $langs): ?string
    {
        $requiredSkills = $project->requiredSkills()->get();
        $requiredCerts = $project->requiredCertifications()->get();
        $requiredLangs = $project->requiredLanguages()->get();

        foreach ($requiredSkills as $req) {
            if (! $req->pivot->is_mandatory) {
                continue;
            }

            $match = $skills->firstWhere('id', $req->id);

            if ($match === null) {
                return "Missing mandatory skill: {$req->name}";
            }

            $empProf = (int) ($match->pivot->proficiency_level ?? 0);
            $reqProf = (int) $req->pivot->minimum_proficiency;

            if ($reqProf > 0 && $empProf < $reqProf) {
                return "Mandatory skill [{$req->name}] proficiency below required ({$empProf}/{$reqProf}).";
            }
        }

        $validCertIds = $certs
            ->filter(fn ($c) => $c->pivot->isCurrentlyValid())
            ->pluck('id')
            ->all();

        foreach ($requiredCerts as $req) {
            if ($req->pivot->is_mandatory && ! in_array($req->id, $validCertIds, true)) {
                return "Missing mandatory certification: {$req->name}";
            }
        }

        $empLangLevels = $langs->mapWithKeys(fn ($l) => [
            $l->id => max(
                self::LEVEL_ORDER[$l->pivot->speaking_level?->value] ?? 0,
                self::LEVEL_ORDER[$l->pivot->writing_level?->value] ?? 0,
                self::LEVEL_ORDER[$l->pivot->reading_level?->value] ?? 0,
            ),
        ]);

        foreach ($requiredLangs as $req) {
            if (! $req->pivot->is_mandatory) {
                continue;
            }

            $requiredLevel = self::LEVEL_ORDER[$req->pivot->minimum_level?->value] ?? 1;

            if (($empLangLevels->get($req->id, 0)) < $requiredLevel) {
                return "Missing mandatory language: {$req->language->name}";
            }
        }

        return null;
    }

    /**
     * Collects human-readable reasons explaining why no candidate could meet
     * the project's mandatory requirements. Computed across the active
     * workforce, so it is independent of the run's assignment mode.
     *
     * @return array<int, string>
     */
    private function mandatoryBlockers(Project $project): array
    {
        $employees = Employee::query()
            ->where('employment_status', EmploymentStatus::Active->value)
            ->with(['skills', 'certifications', 'languages'])
            ->get();

        $reasons = [];

        foreach ($employees as $employee) {
            $reason = $this->mandatoryRequirementReason($project, $employee->skills, $employee->certifications, $employee->languages);

            if ($reason !== null) {
                $reasons[$reason] = true;
            }
        }

        $blockers = array_keys($reasons);

        if ($blockers === [] && $employees->isNotEmpty()) {
            return ['No single candidate satisfies every mandatory requirement combined.'];
        }

        return array_slice($blockers, 0, 8);
    }

    /** @return array{matched: int, total: int, score: float} */
    private function scoreSkillsFromCollection($employeeSkills, Project $project): array
    {
        $required = $project->requiredSkills()->get();

        if ($required->isEmpty()) {
            return ['matched' => 0, 'total' => 0, 'score' => 0.0];
        }

        $empSkillIds = $employeeSkills->pluck('id')->all();
        $totalWeight = $required->sum(fn ($r) => (float) ($r->pivot->weight ?? 1.0));
        $earnedWeight = 0.0;
        $matched = 0;

        foreach ($required as $req) {
            if (in_array($req->id, $empSkillIds, true)) {
                $matched++;
                $empSkill = $employeeSkills->firstWhere('id', $req->id);
                $empProf = (int) ($empSkill?->pivot->proficiency_level ?? 0);
                $reqProf = (int) $req->pivot->minimum_proficiency;
                $proficiencyRatio = $reqProf > 0 ? min($empProf / $reqProf, 1.0) : 1.0;
                $earnedWeight += $proficiencyRatio * (float) ($req->pivot->weight ?? 1.0);
            }
        }

        return [
            'matched' => $matched,
            'total' => $required->count(),
            'score' => $totalWeight > 0 ? $earnedWeight / $totalWeight : 0.0,
        ];
    }

    /** @return array{matched: int, total: int, score: float} */
    private function scoreCertificationsFromCollection($employeeCerts, Project $project): array
    {
        $required = $project->requiredCertifications()->get();

        if ($required->isEmpty()) {
            return ['matched' => 0, 'total' => 0, 'score' => 0.0];
        }

        $empCertIds = $employeeCerts
            ->filter(fn ($c) => $c->pivot->isCurrentlyValid())
            ->pluck('id')
            ->all();
        $matched = 0;

        foreach ($required as $req) {
            if (in_array($req->id, $empCertIds, true)) {
                $matched++;
            }
        }

        return [
            'matched' => $matched,
            'total' => $required->count(),
            'score' => $required->count() > 0 ? $matched / $required->count() : 0.0,
        ];
    }

    /** @return array{matched: int, total: int, score: float} */
    private function scoreLanguagesFromCollection($employeeLangs, Project $project): array
    {
        $required = $project->requiredLanguages()->get();

        if ($required->isEmpty()) {
            return ['matched' => 0, 'total' => 0, 'score' => 0.0];
        }

        $empLangLevels = $employeeLangs->mapWithKeys(fn ($l) => [
            $l->id => max(
                self::LEVEL_ORDER[$l->pivot->speaking_level?->value] ?? 0,
                self::LEVEL_ORDER[$l->pivot->writing_level?->value] ?? 0,
                self::LEVEL_ORDER[$l->pivot->reading_level?->value] ?? 0,
            ),
        ]);

        $matched = 0;

        foreach ($required as $req) {
            $requiredLevel = self::LEVEL_ORDER[$req->pivot->minimum_level?->value] ?? 1;
            $employeeLevel = $empLangLevels->get($req->id, 0);

            if ($employeeLevel >= $requiredLevel) {
                $matched++;
            }
        }

        return [
            'matched' => $matched,
            'total' => $required->count(),
            'score' => $required->count() > 0 ? $matched / $required->count() : 0.0,
        ];
    }

    private function scoreAvailabilityFromCollection($availabilities): float
    {
        $now = now()->toDateString();

        $current = $availabilities
            ->filter(fn ($a) => $a->start_date->toDateString() <= $now && $a->end_date->toDateString() >= $now)
            ->sortByDesc('start_date')
            ->first();

        return $current ? $current->availability_percentage / 100 : self::DEFAULT_AVAILABILITY_SCORE;
    }

    private function scoreWorkloadFromCollection($workloads): float
    {
        $now = now()->toDateString();

        $current = $workloads
            ->filter(fn ($w) => $w->period_start->toDateString() <= $now && $w->period_end->toDateString() >= $now)
            ->sortByDesc('period_start')
            ->first();

        $allocPct = $current?->allocated_percentage ?? 0;

        return 1.0 - ($allocPct / 100);
    }

    /** @return array<string, mixed> */
    private function buildCriteriaSnapshot(Project $project): array
    {
        return [
            'project_id' => $project->id,
            'assignment_mode' => $project->assignment_mode->value,
            'required_skills_count' => $project->requiredSkills()->count(),
            'required_certifications_count' => $project->requiredCertifications()->count(),
            'required_languages_count' => $project->requiredLanguages()->count(),
            'weights' => $this->weights,
        ];
    }
}
