<?php

namespace App\Models;

use Database\Factories\SkillCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
#[Fillable(['name', 'description'])]
class SkillCategory extends Model
{
    /** @use HasFactory<SkillCategoryFactory> */
    use HasFactory;

    /** @return HasMany<Skill, $this> */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }
}
