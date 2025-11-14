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
        return $user->can('reports:view-any') || $user->can('reports:view-own');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        // danru, manajemen, and anggota can view any approved report
        if ($report->status === 'disetujui' && $user->hasRole(['danru', 'manajemen', 'anggota'])) {
            return true;
        }

        if ($user->hasRole('anggota')) {
            return $report->user->hasRole('anggota');
        }

        if ($user->can('reports:view-any')) {
            // If user is danru, they can only view reports from anggota
            if ($user->hasRole('danru')) {
                return $report->user->hasRole(['anggota', 'danru']);
            }
            // Other roles with view-any can see everything
            return true;
        }

        if ($user->can('reports:view-own')) {
            return $user->id === $report->user_id;
        }

        // Allow viewing approved reports if the user has the permission
        if ($user->can('view approved reports') && $report->status === 'disetujui') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('reports:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        if ($user->can('reports:update-any')) {
            return true;
        }

        if ($user->can('reports:update-own')) {
            return $user->id === $report->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        if ($user->can('reports:delete-any')) {
            return true;
        }

        if ($user->can('reports:delete-own')) {
            return $user->id === $report->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->can('reports:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->can('reports:force-delete');
    }

    public function exportMonthly(User $user): bool
    {
        return $user->hasRole('danru') && $user->can('reports:export-monthly');
    }

    public function approve(User $user, Report $report): bool
    {
        if ($user->hasRole('danru')) {
            return $report->user->hasRole(['anggota', 'danru']) && $user->id !== $report->user_id && $user->can('reports:approve');
        }
        return $user->can('reports:approve');
    }

    public function reject(User $user, Report $report): bool
    {
        if ($user->hasRole('danru')) {
            return $report->user->hasRole(['anggota', 'danru']) && $user->id !== $report->user_id && $user->can('reports:reject');
        }
        return $user->can('reports:reject');
    }
}
