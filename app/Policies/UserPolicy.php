<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('superadmin')) {
            // SuperAdmin bisa melakukan semua kecuali menghapus atau mereset password dirinya sendiri
            if (in_array($ability, ['forceDelete']) && $user->id === auth()->user()->id) { // Tambahkan 'forceDelete'
                return false; // SuperAdmin tidak bisa menghapus/reset password/forceDelete dirinya sendiri
            }
            return true;
        }

        return null; // lanjutkan cek method lain
    }


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
        return $user->can('users:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('users:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) return false; // tidak bisa hapus diri sendiri
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
        if ($user->id === $model->id) return false; // tidak bisa hapus diri sendiri
        return $user->can('users:force-delete');
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->can('users:reset-password');
    }
}
