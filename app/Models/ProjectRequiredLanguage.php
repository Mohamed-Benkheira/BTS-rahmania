<?php

namespace App\Models;

use App\Enums\LanguageLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $project_id
 * @property int $language_id
 * @property LanguageLevel|null $minimum_level
 * @property bool $is_mandatory
 */
class ProjectRequiredLanguage extends Pivot
{
    protected $table = 'project_required_languages';

    protected function casts(): array
    {
        return [
            'minimum_level' => LanguageLevel::class,
            'is_mandatory' => 'boolean',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
