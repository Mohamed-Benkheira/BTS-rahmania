<?php

namespace App\Models;

use Database\Factories\ProjectEvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int|null $employee_id
 * @property int $evaluator_id
 * @property int $rating
 * @property int|null $communication_rating
 * @property int|null $delivery_rating
 * @property int|null $quality_rating
 * @property string|null $comments
 * @property Carbon $evaluated_at
 */
#[Fillable(['project_id', 'employee_id', 'evaluator_id', 'rating', 'communication_rating', 'delivery_rating', 'quality_rating', 'comments', 'evaluated_at'])]
class ProjectEvaluation extends Model
{
    /** @use HasFactory<ProjectEvaluationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'communication_rating' => 'integer',
            'delivery_rating' => 'integer',
            'quality_rating' => 'integer',
            'evaluated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
