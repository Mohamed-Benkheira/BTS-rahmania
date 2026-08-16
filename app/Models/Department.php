<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $business_unit_id
 * @property int|null $parent_department_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int|null $manager_id
 * @property bool $is_active
 */
#[Fillable(['business_unit_id', 'parent_department_id', 'name', 'code', 'description', 'manager_id', 'is_active'])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<BusinessUnit, $this> */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id');
    }

    /** @return HasMany<Department, $this> */
    public function subDepartments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_department_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /** @return HasMany<Team, $this> */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
