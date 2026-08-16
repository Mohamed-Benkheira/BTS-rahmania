<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $project_id
 * @property int $skill_id
 * @property int|null $minimum_proficiency
 * @property float|null $minimum_years_experience
 * @property bool $is_mandatory
 * @property float|null $weight
 */
class ProjectRequiredSkill extends Pivot
{
    protected $table = 'project_required_skills';

    protected function casts(): array
    {
        return [
            'minimum_proficiency' => 'integer',
            'minimum_years_experience' => 'float',
            'is_mandatory' => 'boolean',
            'weight' => 'float',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
