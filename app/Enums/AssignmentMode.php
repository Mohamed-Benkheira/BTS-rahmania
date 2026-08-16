<?php

namespace App\Enums;

enum AssignmentMode: string
{
    case SingleEmployee = 'single_employee';
    case MultipleEmployees = 'multiple_employees';
    case Team = 'team';
    case Department = 'department';
}
