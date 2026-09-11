<?php

namespace App\Enums;

enum ProfileChangeType: string
{
    case Profile = 'profile';
    case Skill = 'skill';
    case Language = 'language';
    case Certification = 'certification';
    case Availability = 'availability';
}
