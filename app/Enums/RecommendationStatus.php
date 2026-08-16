<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Overridden = 'overridden';
}
