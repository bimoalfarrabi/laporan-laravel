<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'manajemen', 'danru', 'anggota']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole(['superadmin', 'manajemen', 'danru', 'anggota'])) {
            return true;
        }

        return $user->id === $leaveRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['danru', 'anggota']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only the owner can edit, and only if it's still pending.
        return $user->id === $leaveRequest->user_id && $leaveRequest->status === 'menunggu persetujuan';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only the owner can delete, and only if it's still pending.
        return $user->id === $leaveRequest->user_id && $leaveRequest->status === 'menunggu persetujuan';
    }

    /**
     * Determine whether the user can approve or reject the model.
     */
    public function approveOrReject(User $user, LeaveRequest $leaveRequest): bool
    {
        // A 'danru' can approve or reject, but not their own request.
        return $user->hasRole('danru') && $user->id !== $leaveRequest->user_id;
    }

    /**
     * Determine whether the user can export the model to PDF.
     */
    public function exportPdf(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only approved leave requests can be exported
        if ($leaveRequest->status !== 'disetujui') {
            return false;
        }

        // Superadmin, Manajemen, and Danru can export any approved leave request
        return $user->hasRole(['superadmin', 'manajemen', 'danru']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['superadmin']);
    }
}
