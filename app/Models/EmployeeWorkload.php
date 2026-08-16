<?php

namespace App\Models;

use Database\Factories\EmployeeWorkloadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property float $allocated_percentage
 * @property float|null $allocated_hours
 * @property string $source
 */
#[Fillable(['employee_id', 'period_start', 'period_end', 'allocated_percentage', 'allocated_hours', 'source'])]
class EmployeeWorkload extends Model
{
    /** @use HasFactory<EmployeeWorkloadFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'allocated_percentage' => 'float',
            'allocated_hours' => 'float',
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
