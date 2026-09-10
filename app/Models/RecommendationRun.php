<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $executed_by
 * @property string $algorithm_version
 * @property array<string, mixed> $criteria_snapshot
 * @property array<int, string>|null $blockers
 * @property Carbon $executed_at
 */
#[Fillable(['project_id', 'executed_by', 'algorithm_version', 'criteria_snapshot', 'blockers', 'executed_at'])]
class RecommendationRun extends Model
{
    protected function casts(): array
    {
        return [
            'criteria_snapshot' => 'array',
            'blockers' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /** @return HasMany<Recommendation, $this> */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
