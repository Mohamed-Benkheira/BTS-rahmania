<?php

namespace App\Enums;

enum LanguageLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case Native = 'native';
}
