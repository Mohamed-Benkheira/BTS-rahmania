<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $project_id
 * @property int $certification_id
 * @property bool $is_mandatory
 */
class ProjectRequiredCertification extends Pivot
{
    protected $table = 'project_required_certifications';

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Certification, $this> */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }
}
