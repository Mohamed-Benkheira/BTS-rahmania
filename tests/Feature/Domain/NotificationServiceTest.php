<?php

namespace Tests\Feature\Domain;

use App\Enums\ProfileChangeType;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use App\Notifications\ProfileChangeResolved;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function resolution(): ProfileChangeResolved
    {
        return new ProfileChangeResolved(
            ProfileChangeRequest::factory()->ofType(ProfileChangeType::Profile)->create()
        );
    }

    public function test_to_reviewers_notifies_hr_only_and_admin_and_super_admin(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole(Role::firstOrCreate(['name' => 'hr']));

        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super-admin']));

        $other = User::factory()->create();
        $other->assignRole(Role::firstOrCreate(['name' => 'project-manager']));

        app(NotificationService::class)->toReviewers($this->resolution());

        $this->assertSame(1, $hr->notifications()->count());
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $superAdmin->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());
    }

    public function test_to_reviewers_excludes_the_actor(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole(Role::firstOrCreate(['name' => 'hr']));

        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        app(NotificationService::class)->toReviewers($this->resolution(), $hr);

        $this->assertSame(0, $hr->notifications()->count());
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_survives_missing_roles(): void
    {
        User::factory()->create();

        app(NotificationService::class)->toReviewers($this->resolution());

        $this->assertTrue(true);
    }
}
