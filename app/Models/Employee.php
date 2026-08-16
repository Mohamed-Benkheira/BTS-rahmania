<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $employee_code
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property Carbon|null $birth_date
 * @property Carbon|null $hire_date
 * @property EmploymentType $employment_type
 * @property EmploymentStatus $employment_status
 * @property int|null $business_unit_id
 * @property int|null $department_id
 * @property int|null $team_id
 * @property int|null $position_id
 * @property int|null $primary_location_id
 * @property int|null $manager_id
 * @property string|null $biography
 * @property string|null $profile_photo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'user_id',
    'employee_code',
    'first_name',
    'last_name',
    'phone',
    'birth_date',
    'hire_date',
    'employment_type',
    'employment_status',
    'business_unit_id',
    'department_id',
    'team_id',
    'position_id',
    'primary_location_id',
    'manager_id',
    'biography',
    'profile_photo_path',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'employment_type' => EmploymentType::class,
            'employment_status' => EmploymentStatus::class,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<BusinessUnit, $this> */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /** @return HasMany<Employee, $this> */
    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /** @return BelongsToMany<Skill, $this, EmployeeSkill> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'employee_skills')
            ->using(EmployeeSkill::class)
            ->withPivot(['proficiency_level', 'years_experience', 'last_used_at', 'verified_at', 'verified_by', 'notes'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Certification, $this, EmployeeCertification> */
    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'employee_certifications')
            ->using(EmployeeCertification::class)
            ->withPivot(['certificate_number', 'issued_at', 'expires_at', 'document_path', 'verification_status'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Language, $this, EmployeeLanguage> */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'employee_languages')
            ->using(EmployeeLanguage::class)
            ->withPivot(['speaking_level', 'writing_level', 'reading_level'])
            ->withTimestamps();
    }

    /** @return HasMany<EmployeeAvailability, $this> */
    public function availabilities(): HasMany
    {
        return $this->hasMany(EmployeeAvailability::class);
    }

    /** @return HasMany<EmployeeWorkload, $this> */
    public function workloads(): HasMany
    {
        return $this->hasMany(EmployeeWorkload::class);
    }

    /** @return BelongsToMany<Assignment, $this, AssignmentMember> */
    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_members')
            ->using(AssignmentMember::class)
            ->withPivot(['responsibility', 'allocation_percentage', 'allocated_hours', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /** @return HasMany<Team, $this> */
    public function teamLeaderOfTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_leader_id');
    }

    /** @return HasMany<ProjectEvaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(ProjectEvaluation::class);
    }

    /** @param Builder<Employee> $query
     *  @return Builder<Employee> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', EmploymentStatus::Active->value);
    }
}
