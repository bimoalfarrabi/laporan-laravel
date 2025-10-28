<?php

namespace App\Policies;

use App\Models\LaporanHarianJaga;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LaporanHarianJagaPolicy
{

    /**
    * Perform pre-authorization checks.
    * Ini akan dijalankan sebelum metode otorisasi lainnya.
    * Jika SuperAdmin, langsung izinkan semua.
    */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        return null; // Lanjutkan ke metode otorisasi lainnya
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('laporan-harian-jaga:view-any') || $user->can('laporan-harian-jaga:view-own');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        if ($user->can('laporan-harian-jaga:view-any')) {
            return true;
        }

        if ($user->can('laporan-harian-jaga:view-own')) {
            return $user->id === $laporanHarianJaga->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('laporan-harian-jaga:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        if ($user->can('laporan-harian-jaga:update-any')) {
            return true;
        }

        if ($user->can('laporan-harian-jaga:update-own')) {
            return $user->id === $laporanHarianJaga->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        if ($user->can('laporan-harian-jaga:delete-any')) {
            return true;
        }

        if ($user->can('laporan-harian-jaga:delete-own')) {
            return $user->id === $laporanHarianJaga->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        return $user->can('laporan-harian-jaga:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        return $user->can('laporan-harian-jaga:force-delete');
    }
}
