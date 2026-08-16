<?php

namespace App\Observers;

use App\Observers\Concerns\AuditsChanges;

class ProjectObserver
{
    use AuditsChanges;

    protected function event(string $action): string
    {
        return "project.{$action}";
    }
}
