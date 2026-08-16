<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Database\Factories\EmployeeAvailabilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property float $availability_percentage
 * @property AvailabilityStatus $status
 * @property string|null $reason
 * @property string|null $notes
 */
#[Fillable(['employee_id', 'start_date', 'end_date', 'availability_percentage', 'status', 'reason', 'notes'])]
class EmployeeAvailability extends Model
{
    /** @use HasFactory<EmployeeAvailabilityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'availability_percentage' => 'float',
            'status' => AvailabilityStatus::class,
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
