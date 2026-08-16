<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case PartiallyAvailable = 'partially_available';
    case Unavailable = 'unavailable';
    case OnLeave = 'on_leave';
}
