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

        $this->createUsers();

        $this->call(ProjectSeeder::class);
        $this->call(HistorySeeder::class);
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
        $links = EmployeeSeeder::reservedLinks();

        foreach ($links as $email => $employeeCode) {
            $user = User::where('email', $email)->first();
            $employee = Employee::where('employee_code', $employeeCode)->first();

            if ($user !== null && $employee !== null && $employee->user_id === null) {
                $employee->update(['user_id' => $user->id]);
            }
        }
    }
}
