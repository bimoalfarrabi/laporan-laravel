<?php

namespace App\Policies;

use App\Models\ReportType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportTypePolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'superadmin') {
            return true; // hanya super admin yg bisa mengelola jenis laporan
        }

        return null; // Lanjutkan ke metode otorisasi lainnya
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReportType $reportType): bool
    {
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
    public function update(User $user, ReportType $reportType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReportType $reportType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ReportType $reportType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ReportType $reportType): bool
    {
        return false;
    }
}
