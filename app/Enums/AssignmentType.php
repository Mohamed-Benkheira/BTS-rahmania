<?php

namespace App\Enums;

enum AssignmentType: string
{
    case Employee = 'employee';
    case Team = 'team';
    case Department = 'department';
    case Mixed = 'mixed';
}
