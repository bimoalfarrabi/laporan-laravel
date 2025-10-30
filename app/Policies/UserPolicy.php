<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users:view-any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('users:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('danru')) {
            return true;
        }
        return $user->can('users:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // A superadmin cannot be updated by anyone, not even another superadmin.
        if ($model->hasRole('superadmin')) {
            return false;
        }

        return $user->can('users:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // A superadmin cannot be deleted.
        if ($model->hasRole('superadmin')) {
            return false;
        }

        // A user cannot delete themselves.
        if ($user->id === $model->id) {
            return false;
        }

        // Danru can delete anggota in the same shift
        if ($user->hasRole('danru') && $model->hasRole('anggota') && $user->shift === $model->shift) {
            return true;
        }

        return $user->can('users:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('users:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // A superadmin cannot be force-deleted.
        if ($model->hasRole('superadmin')) {
            return false;
        }

        // A user cannot force-delete themselves.
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users:force-delete');
    }

    /**
     * Determine whether the user can reset the password for the model.
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Per the user's request, resetting a superadmin's password is allowed.
        // The Spatie Gate::before check will grant this to superadmins.
        // For other roles, it depends on the permission.
        return $user->can('users:reset-password');
    }
}