<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{

    /**
     * Perform pre-authorization checks.
     * super admin diizinkan semua
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
        // danru dan anggotas dapat melihat daftar laporan
        return $user->hasRole(['danru', 'anggota']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        // danru bisa melihat semua laporan
        if ($user->hasRole('danru')) {
            return true;
        }

        // anggota hanya bisa melihat laporannya sendiri
        return $user->id === $report->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // danru dan anggota bisa membuat laporan
        return $user->hasRole(['danru', 'anggota']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        // danru bisa mengupdate semua laporan
        if ($user->hasRole('danru')) {
            return true;
        }

        // anggota hanya bisa mengupdate laporannya sendiri
        return $user->id === $report->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        // danru bisa menghapus semua laporan
        if ($user->hasRole('danru')) {
            return true;
        }

        // anggota hanya bisa menghapus laporannya sendiri
        return $user->id === $report->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        // hanya danru yang bisa mengembalikan laporan
        return $user->hasRole('danru');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        // hanya super admin yang bisa menghapus permanen laporan
        return false;
    }
}
