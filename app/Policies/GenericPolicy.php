<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class GenericPolicy
{
    use HandlesAuthorization;

    /**
     * The permission resource label, e.g. "employees".
     */
    protected static string $resource = '';

    protected function permission(User $user, string $action): bool
    {
        return $user->can($action.' '.static::$resource);
    }

    public function viewAny(User $user): bool
    {
        return $this->permission($user, 'view');
    }

    public function view(User $user, object $model): bool
    {
        return $this->permission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->permission($user, 'create');
    }

    public function update(User $user, object $model): bool
    {
        return $this->permission($user, 'update');
    }

    public function delete(User $user, object $model): bool
    {
        return $this->permission($user, 'delete');
    }

    public function restore(User $user, object $model): bool
    {
        return $this->permission($user, 'update');
    }

    public function forceDelete(User $user, object $model): bool
    {
        return $this->permission($user, 'delete');
    }
}
