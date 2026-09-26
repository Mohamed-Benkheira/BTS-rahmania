<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('email', 'admin@djezzy.test')->firstOrFail();
    }

    public function test_admin_can_access_audit_logs_index(): void
    {
        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'action' => 'project.created',
            'auditable_type' => 'App\Models\Project',
            'auditable_id' => 1,
            'old_values' => null,
            'new_values' => ['name' => 'HR Talent Portal'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test Agent',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/audit-logs');

        $response->assertOk();
        $response->assertSee('Audit Logs');
        $response->assertSee('project.created');
    }

    public function test_admin_can_view_single_audit_log_details(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'action' => 'employee.updated',
            'auditable_type' => Employee::class,
            'auditable_id' => 10,
            'old_values' => ['phone' => '0550000000'],
            'new_values' => ['phone' => '0551111111'],
            'ip_address' => '10.0.0.42',
            'user_agent' => 'Mozilla/5.0 Test Suite',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/audit-logs/{$log->id}");

        $response->assertOk();
        $response->assertSee('Audit Event Metadata');
        $response->assertSee('Payload Changes');
        $response->assertSee('10.0.0.42');
        $response->assertSee('employee.updated');
    }

    public function test_audit_logs_cannot_be_created_or_edited_via_http(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'action' => 'project.created',
            'auditable_type' => 'App\Models\Project',
            'auditable_id' => 1,
            'new_values' => ['name' => 'Test'],
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertFalse(\App\Filament\Admin\Resources\AuditLogs\AuditLogResource::canCreate());
        $this->assertFalse(\App\Filament\Admin\Resources\AuditLogs\AuditLogResource::canEdit($log));
        $this->assertFalse(\App\Filament\Admin\Resources\AuditLogs\AuditLogResource::canDelete($log));
        $this->assertFalse(\App\Filament\Admin\Resources\AuditLogs\AuditLogResource::canDeleteAny());

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('filament.admin.resources.audit-logs.create'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('filament.admin.resources.audit-logs.edit'));

        $this->actingAs($this->adminUser)->get("/admin/audit-logs/{$log->id}/edit")->assertNotFound();
    }

    public function test_regular_employee_cannot_access_audit_logs(): void
    {
        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('employee');

        $this->actingAs($employeeUser)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_editing_department_creates_audit_log_and_appears_in_resource(): void
    {
        $department = \App\Models\Department::firstOrFail();
        $originalName = $department->name;

        $department->update(['name' => 'Audited Engineering Division']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'department.updated',
            'auditable_type' => \App\Models\Department::class,
            'auditable_id' => $department->id,
        ]);

        $log = AuditLog::where('action', 'department.updated')
            ->where('auditable_id', $department->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($originalName, $log->old_values['name']);
        $this->assertSame('Audited Engineering Division', $log->new_values['name']);

        $response = $this->actingAs($this->adminUser)->get('/admin/audit-logs');
        $response->assertOk();
        $response->assertSee('department.updated');
    }
}
