<?php

namespace App\Enums;

enum ConfidentialityLevel: string
{
    case Public = 'public';
    case Internal = 'internal';
    case Confidential = 'confidential';
    case Restricted = 'restricted';
}
