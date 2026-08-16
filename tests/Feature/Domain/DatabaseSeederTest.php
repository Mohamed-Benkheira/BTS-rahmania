<?php

namespace Tests\Feature\Domain;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\BusinessUnit;
use App\Models\Certification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAvailability;
use App\Models\EmployeeWorkload;
use App\Models\Language;
use App\Models\Location;
use App\Models\Position;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_domain_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(3, BusinessUnit::count());
        $this->assertGreaterThanOrEqual(6, Department::count());
        $this->assertGreaterThanOrEqual(5, Team::count());
        $this->assertGreaterThanOrEqual(8, Position::count());
        $this->assertGreaterThanOrEqual(4, Location::count());
        $this->assertGreaterThanOrEqual(10, Employee::count());
        $this->assertGreaterThanOrEqual(20, Skill::count());
        $this->assertGreaterThanOrEqual(5, Certification::count());
        $this->assertGreaterThanOrEqual(5, Language::count());
        $this->assertGreaterThanOrEqual(5, EmployeeAvailability::count());
        $this->assertGreaterThanOrEqual(5, EmployeeWorkload::count());
        $this->assertGreaterThanOrEqual(5, ProjectCategory::count());
        $this->assertGreaterThanOrEqual(5, Project::count());
        $this->assertGreaterThanOrEqual(1, Assignment::count());

        $this->assertTrue(Role::where('name', 'super-admin')->exists());
        $this->assertTrue(User::where('email', 'admin@djezzy.test')->firstOrFail()->hasRole('super-admin'));
        $this->assertTrue(Employee::active()->count() >= 8);
    }

    public function test_database_seeder_creates_a_user_for_each_main_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $users = [
            'admin@example.com' => 'admin',
            'hr@example.com' => 'hr',
            'resources@example.com' => 'resource-manager',
            'projects@example.com' => 'project-manager',
            'employee@example.com' => 'employee',
        ];

        foreach ($users as $email => $role) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "User [{$email}] was not seeded.");
            $this->assertTrue($user->hasRole($role), "User [{$email}] does not have the [{$role}] role.");
        }
    }

    public function test_database_seeder_links_role_users_to_employee_profiles(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['hr@example.com', 'resources@example.com', 'projects@example.com', 'employee@example.com'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertNotNull($user->employee, "User [{$email}] has no linked employee profile.");
        }
    }

    public function test_database_seeder_records_audit_logs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, AuditLog::where('action', 'like', 'employee.%')->count());
        $this->assertGreaterThan(0, AuditLog::where('action', 'like', 'project.%')->count());
    }
}
