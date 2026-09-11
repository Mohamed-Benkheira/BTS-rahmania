<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Spatie\Permission\Models\Role;

class NotificationService
{
    private const REVIEWER_ROLES = ['hr', 'admin', 'super-admin'];

    public function toReviewers(Notification $notification, ?User $exclude = null): void
    {
        $roleNames = Role::query()->whereIn('name', self::REVIEWER_ROLES)->pluck('name');

        if ($roleNames->isEmpty()) {
            return;
        }

        $users = User::role($roleNames->all())->get()
            ->reject(fn (User $user) => $exclude?->id === $user->id);

        $this->toUsers($users, $notification);
    }

    /** @param iterable<User> $users */
    public function toUsers(iterable $users, Notification $notification): void
    {
        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
