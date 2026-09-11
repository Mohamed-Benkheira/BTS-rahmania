<?php

namespace Tests\Feature\Portal;

use App\Enums\ProfileChangeType;
use App\Models\Employee;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use App\Notifications\ProfileChangeResolved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    private function employeeUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'employee']));

        Employee::factory()->active()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_employee_with_linked_profile_can_open_portal_dashboard(): void
    {
        $user = $this->employeeUser();

        $this->actingAs($user)
            ->get('/portal')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('portal/dashboard'));
    }

    public function test_employee_without_linked_profile_is_redirected_home(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'employee']));

        $this->actingAs($user)
            ->get('/portal')
            ->assertRedirect('/');
    }

    public function test_admin_without_employee_role_is_redirected_home(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($user)
            ->get('/portal')
            ->assertRedirect('/');
    }

    public function test_portal_pages_render_for_employee(): void
    {
        $user = $this->employeeUser();

        foreach (['profile', 'skills', 'languages', 'certifications', 'availability', 'my-requests', 'my-assignments', 'my-evaluations', 'notifications'] as $page) {
            $this->actingAs($user)->get("/portal/{$page}")->assertSuccessful();
        }
    }

    public function test_employee_submits_profile_change_request(): void
    {
        $user = $this->employeeUser();

        $this->actingAs($user)
            ->post('/portal/profile', [
                'phone' => '+213 550 12 34 56',
                'biography' => 'Trained network engineer.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('profile_change_requests', [
            'employee_id' => $user->employee->id,
            'type' => 'profile',
            'status' => 'pending',
        ]);
    }

    public function test_employee_submits_skill_change_request_requires_skill(): void
    {
        $user = $this->employeeUser();

        $this->actingAs($user)
            ->post('/portal/skills', ['proficiency_level' => 4])
            ->assertRedirect()
            ->assertSessionHasErrors('skill');
    }

    public function test_unread_count_endpoint_returns_json(): void
    {
        $user = $this->employeeUser();

        $this->actingAs($user)
            ->get('/portal/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 0]);

        $user->notify(new ProfileChangeResolved(
            ProfileChangeRequest::factory()->ofType(ProfileChangeType::Profile)->create(['employee_id' => $user->employee->id]),
        ));

        $this->actingAs($user)
            ->get('/portal/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 1]);
    }
}
