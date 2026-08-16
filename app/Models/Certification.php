<?php

namespace App\Models;

use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string|null $issuer
 * @property string $slug
 * @property string|null $description
 * @property int|null $validity_period_months
 * @property bool $is_active
 */
#[Fillable(['name', 'issuer', 'slug', 'description', 'validity_period_months', 'is_active'])]
class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'validity_period_months' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $certification) {
            if (empty($certification->slug)) {
                $certification->slug = Str::slug($certification->name).'-'.Str::random(4);
            }
        });
    }

    /** @return HasMany<EmployeeCertification, $this> */
    public function employeeCertifications(): HasMany
    {
        return $this->hasMany(EmployeeCertification::class);
    }
}
