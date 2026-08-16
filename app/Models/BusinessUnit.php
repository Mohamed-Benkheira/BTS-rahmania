<?php

namespace App\Models;

use Database\Factories\BusinessUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 */
#[Fillable(['name', 'code', 'description', 'is_active'])]
class BusinessUnit extends Model
{
    /** @use HasFactory<BusinessUnitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
