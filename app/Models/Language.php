<?php

namespace App\Models;

use Database\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 */
#[Fillable(['name', 'code'])]
class Language extends Model
{
    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    /** @return HasMany<EmployeeLanguage, $this> */
    public function employeeLanguages(): HasMany
    {
        return $this->hasMany(EmployeeLanguage::class);
    }
}
