<?php

namespace App\Enums;

enum RequirementType: string
{
    case Skill = 'skill';
    case Certification = 'certification';
    case Language = 'language';
    case Experience = 'experience';
    case Position = 'position';
    case Department = 'department';
    case Location = 'location';
    case Availability = 'availability';
}
