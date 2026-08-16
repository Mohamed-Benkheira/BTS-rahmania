<?php

namespace App\Support;

use App\Enums\AssignmentStatus;
use App\Enums\AvailabilityStatus;
use App\Enums\ProjectStatus;
use BackedEnum;
use Illuminate\Support\Str;

class EnumLabels
{
    public static function projectStatus(ProjectStatus $status): string
    {
        return static::friendly($status);
    }

    public static function assignmentStatus(AssignmentStatus $status): string
    {
        return static::friendly($status);
    }

    public static function availabilityStatus(AvailabilityStatus $status): string
    {
        return static::friendly($status);
    }

    public static function friendly(BackedEnum $value): string
    {
        return Str::headline(Str::replace('_', ' ', $value->value));
    }
}
