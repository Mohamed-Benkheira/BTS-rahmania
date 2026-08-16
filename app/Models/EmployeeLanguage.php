<?php

namespace App\Models;

use App\Enums\LanguageLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $language_id
 * @property LanguageLevel|null $speaking_level
 * @property LanguageLevel|null $writing_level
 * @property LanguageLevel|null $reading_level
 */
class EmployeeLanguage extends Pivot
{
    protected $table = 'employee_languages';

    protected function casts(): array
    {
        return [
            'speaking_level' => LanguageLevel::class,
            'writing_level' => LanguageLevel::class,
            'reading_level' => LanguageLevel::class,
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
