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
        //danru dapat melihat daftar pengguna (di-filter di controller)
        return $user->hasRole('danru');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        //danru dapat melihat detail pengguna
        if ($user->hasRole('danru')) {
            return true;
        }

        //anggota tidak dapat melihat detail pengguna lain
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // superadmin tidak bisa hapus diri sendiri
        if ($user->hasRole('superadmin') && $user->id === $model->id) {
            return false;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->hasRole('superadmin');
    }
}
