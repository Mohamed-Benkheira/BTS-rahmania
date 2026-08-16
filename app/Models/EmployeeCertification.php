<?php

namespace App\Models;

use App\Enums\CertificationVerificationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $certification_id
 * @property string|null $certificate_number
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 * @property string|null $document_path
 * @property CertificationVerificationStatus $verification_status
 */
class EmployeeCertification extends Pivot
{
    protected $table = 'employee_certifications';

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'verification_status' => CertificationVerificationStatus::class,
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Certification, $this> */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function isCurrentlyValid(): bool
    {
        return $this->verification_status === CertificationVerificationStatus::Verified
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
