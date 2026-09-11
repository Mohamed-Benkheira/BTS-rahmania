<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_employee_is_sent_to_portal(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'employee']));
        Employee::factory()->active()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/')->assertRedirect('/portal');
    }

    public function test_employee_without_linked_profile_is_sent_to_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'employee']));

        $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
    }

    public function test_admin_is_sent_to_filament_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($user)->get('/')->assertRedirect('/admin');
    }

    public function test_user_without_roles_is_sent_to_filament_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect('/admin');
    }
}
