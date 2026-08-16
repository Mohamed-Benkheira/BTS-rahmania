<?php

namespace App\Models;

use App\Enums\RequirementType;
use Database\Factories\ProjectRequirementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string|null $description
 * @property RequirementType $requirement_type
 * @property bool $is_mandatory
 * @property float $weight
 */
#[Fillable(['project_id', 'title', 'description', 'requirement_type', 'is_mandatory', 'weight'])]
class ProjectRequirement extends Model
{
    /** @use HasFactory<ProjectRequirementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requirement_type' => RequirementType::class,
            'is_mandatory' => 'boolean',
            'weight' => 'float',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
