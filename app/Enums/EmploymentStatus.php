<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Retired = 'retired';
}
