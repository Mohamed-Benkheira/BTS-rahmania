<?php

namespace App\Enums;

enum CertificationVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
