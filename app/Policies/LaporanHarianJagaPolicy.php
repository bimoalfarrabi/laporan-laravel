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
        // super admin sudah di-handle di metode before
        return in_array($user->role, ['danru', 'anggota']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        // super admin sudah di-handle di metode before
        // danru bisa melihat semua laporan
        if ($user->role === 'danru') {
            return true;
        }

        // anggota hanya bisa melihat laporannya sendiri
        return $user->id === $laporanHarianJaga->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // super admin sudah di-handle di metode before
        // danru dan anggota bisa membuat laporan
        return in_array($user->role, ['danru', 'anggota']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        // super admin sudah di-handle di metode before
        // danru bisa update semua laporan
        if ($user->role === 'danru') {
            return true;
        }

        // anggota hanya bisa mengupdate laporannya sendiri
        return $user->id === $laporanHarianJaga->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        // super admin sudah di-handle di metode before
        // danru bisa menghapus semua laporan
        if ($user->role === 'danru') {
            return true;
        }

        // anggota hanya bisa menghapus laporannya sendiri
        return $user->id === $laporanHarianJaga->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        // super admin sudah di-handle di metode before
        // danru dapat restore laporan, apabila ada soft delete
        return $user->role === 'danru';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LaporanHarianJaga $laporanHarianJaga): bool
    {
        // jangan force delete
        return false;
    }
}
