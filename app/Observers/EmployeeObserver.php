<?php

namespace App\Observers;

use App\Observers\Concerns\AuditsChanges;

class EmployeeObserver
{
    use AuditsChanges;

    protected function event(string $action): string
    {
        return "employee.{$action}";
    }
}
