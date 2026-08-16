<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $skill_category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property Carbon|null $deleted_at
 */
#[Fillable(['skill_category_id', 'name', 'slug', 'description', 'is_active'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $skill) {
            if (empty($skill->slug)) {
                $skill->slug = Str::slug($skill->name).'-'.Str::random(4);
            }
        });
    }

    /** @return BelongsTo<SkillCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /** @return HasMany<EmployeeSkill, $this> */
    public function employeeSkills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    /** @return BelongsToMany<Employee, $this, EmployeeSkill> */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_skills')
            ->using(EmployeeSkill::class)
            ->withPivot(['proficiency_level', 'years_experience', 'last_used_at', 'verified_at', 'verified_by', 'notes'])
            ->withTimestamps();
    }
}
