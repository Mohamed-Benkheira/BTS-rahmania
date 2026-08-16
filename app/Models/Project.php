<?php

namespace App\Models;

use App\Enums\AssignmentMode;
use App\Enums\AssignmentStatus;
use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $project_code
 * @property string $name
 * @property string|null $description
 * @property int $category_id
 * @property int|null $requesting_department_id
 * @property int|null $owning_department_id
 * @property int|null $project_manager_id
 * @property int $created_by
 * @property ProjectPriority $priority
 * @property ProjectStatus $status
 * @property ConfidentialityLevel $confidentiality_level
 * @property Carbon|null $start_date
 * @property Carbon|null $target_end_date
 * @property float|null $estimated_hours
 * @property float|null $estimated_budget
 * @property int $required_members_count
 * @property AssignmentMode $assignment_mode
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'project_code',
    'name',
    'description',
    'category_id',
    'requesting_department_id',
    'owning_department_id',
    'project_manager_id',
    'created_by',
    'priority',
    'status',
    'confidentiality_level',
    'start_date',
    'target_end_date',
    'estimated_hours',
    'estimated_budget',
    'required_members_count',
    'assignment_mode',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'priority' => ProjectPriority::class,
            'status' => ProjectStatus::class,
            'confidentiality_level' => ConfidentialityLevel::class,
            'assignment_mode' => AssignmentMode::class,
            'start_date' => 'date',
            'target_end_date' => 'date',
            'estimated_hours' => 'float',
            'estimated_budget' => 'float',
            'required_members_count' => 'integer',
        ];
    }

    /** @return BelongsTo<ProjectCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    /** @return BelongsTo<Department, $this> */
    public function requestingDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requesting_department_id');
    }

    /** @return BelongsTo<Department, $this> */
    public function owningDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'owning_department_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<ProjectRequirement, $this> */
    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class);
    }

    /** @return BelongsToMany<Skill, $this, ProjectRequiredSkill> */
    public function requiredSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_required_skills')
            ->using(ProjectRequiredSkill::class)
            ->withPivot(['minimum_proficiency', 'minimum_years_experience', 'is_mandatory', 'weight'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Certification, $this, ProjectRequiredCertification> */
    public function requiredCertifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'project_required_certifications')
            ->using(ProjectRequiredCertification::class)
            ->withPivot(['is_mandatory'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Language, $this, ProjectRequiredLanguage> */
    public function requiredLanguages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'project_required_languages')
            ->using(ProjectRequiredLanguage::class)
            ->withPivot(['minimum_level', 'is_mandatory'])
            ->withTimestamps();
    }

    /** @return HasMany<Assignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /** @return HasMany<ProjectEvaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(ProjectEvaluation::class);
    }

    public function activeAssignment(): ?Assignment
    {
        return $this->assignments()
            ->whereIn('status', [AssignmentStatus::Active->value])
            ->latest('id')
            ->first();
    }
}
