<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(TraitSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ProjectSeeder::class);

        $this->createUsers();
    }

    private function createUsers(): void
    {
        $users = [
            ['name' => 'Djezzy Administrator', 'email' => 'admin@djezzy.test', 'role' => 'super-admin'],
            ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'HR Manager', 'email' => 'hr@example.com', 'role' => 'hr'],
            ['name' => 'Resource Manager', 'email' => 'resources@example.com', 'role' => 'resource-manager'],
            ['name' => 'Project Manager', 'email' => 'projects@example.com', 'role' => 'project-manager'],
            ['name' => 'Regular Employee', 'email' => 'employee@example.com', 'role' => 'employee'],
        ];

        foreach ($users as $user) {
            $model = User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $model->assignRole($user['role']);
        }

        $this->linkUsersToEmployees();
    }

    private function linkUsersToEmployees(): void
    {
        $link = function (string $email, array $constraints): void {
            $user = User::where('email', $email)->first();

            if ($user === null) {
                return;
            }

            $query = Employee::query()->whereNull('user_id')->orderBy('id');

            if (isset($constraints['department'])) {
                $query->whereHas('department', fn ($q) => $q->where('code', $constraints['department']));
            }

            if (isset($constraints['position'])) {
                $query->whereHas('position', fn ($q) => $q->where('code', $constraints['position']));
            }

            $employee = $query->first();

            if ($employee !== null) {
                $employee->update(['user_id' => $user->id]);
            }
        };

        $link('hr@example.com', ['department' => 'DEPT-HR']);
        $link('resources@example.com', ['department' => 'DEPT-IT']);
        $link('projects@example.com', ['position' => 'POS-PM']);
        $link('employee@example.com', []);
    }
}
