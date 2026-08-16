<?php

namespace App\Models;

use App\Enums\RecommendationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $recommendation_run_id
 * @property int|null $employee_id
 * @property int|null $team_id
 * @property int|null $department_id
 * @property float $total_score
 * @property int $rank
 * @property string $eligibility_status
 * @property array<string, mixed>|null $explanation
 * @property RecommendationStatus $status
 */
#[Fillable(['recommendation_run_id', 'employee_id', 'team_id', 'department_id', 'total_score', 'rank', 'eligibility_status', 'explanation', 'status'])]
class Recommendation extends Model
{
    protected function casts(): array
    {
        return [
            'total_score' => 'float',
            'rank' => 'integer',
            'explanation' => 'array',
            'status' => RecommendationStatus::class,
        ];
    }

    /** @return BelongsTo<RecommendationRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(RecommendationRun::class, 'recommendation_run_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
