<?php

namespace App\Console\Commands;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class EnsureE2EEmployee extends Command
{
    protected $signature = 'app:ensure-e2e-employee';

    protected $description = 'Create (or reuse) an active employee with portal access for e2e tests';

    public function handle(): int
    {
        $user = User::query()
            ->where('email', 'e2e.employee@djezzy.test')
            ->first();

        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'E2E Employee',
                'email' => 'e2e.employee@djezzy.test',
                'password' => 'password',
            ]);
        }

        $role = Role::query()->where('name', 'employee')->first()
            ?? Role::create(['name' => 'employee']);

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        if ($user->employee === null) {
            Employee::query()->create([
                'user_id' => $user->id,
                'employee_code' => 'E2E-'.now()->format('YmdHis'),
                'first_name' => 'E2E',
                'last_name' => 'Employee',
                'phone' => '+213 500 00 00 00',
                'hire_date' => now()->subYear()->toDateString(),
                'employment_type' => EmploymentType::FullTime->value,
                'employment_status' => EmploymentStatus::Active->value,
                'department_id' => Department::query()->value('id'),
                'biography' => 'Automated browser QA fixture.',
            ]);
        }

        $this->line('e2e.employee@djezzy.test');

        return self::SUCCESS;
    }
}
