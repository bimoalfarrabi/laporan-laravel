<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForcePasswordChangeController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Route untuk menyajikan file dari storage
Route::get('storage/files/{path}', [FileController::class, 'serve'])
    ->where('path', '.*')
    ->name('files.serve');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// perubahan password (diluar grup auth)
Route::get('/force-password-change', [ForcePasswordChangeController::class, 'showChangeForm'])->name('password.force-change');
Route::post('/force-password-change', [ForcePasswordChangeController::class, 'updatePassword'])->name('password.update-forced');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.updateTheme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/check', [App\Http\Controllers\NotificationController::class, 'check'])->name('notifications.check');
    Route::post('/notifications/subscribe', [App\Http\Controllers\NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::get('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Manajemen Jenis Laporan
    Route::get('report-types/explanation', [ReportTypeController::class, 'explanation'])->name('report-types.explanation');
    Route::resource('report-types', ReportTypeController::class);

    // Laporan
    Route::get('/reports/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::get('/reports/export', [ReportController::class, 'showExportForm'])->name('reports.export');
    Route::get('/reports/export-monthly-pdf/{year}/{month}', [ReportController::class, 'exportMonthlyPdf'])->name('reports.exportMonthlyPdf');
    Route::get('/reports/{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');
    Route::resource('reports', ReportController::class)->withTrashed();
    Route::post('/reports/{report}/rotate-image', [ReportController::class, 'rotateImage'])->name('reports.rotateImage');
    Route::post('/reports/{id}/restore', [ReportController::class, 'restore'])->name('reports.restore');
    Route::delete('/reports/{id}/force-delete', [ReportController::class, 'forceDelete'])->name('reports.forceDelete');

    // Rute untuk approve dan reject laporan
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

    // Rute untuk export PDF laporan
    Route::get('/reports/export', [ReportController::class, 'showExportForm'])->name('reports.export');
    Route::get('/reports/{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');

    // Rute untuk export PDF laporan bulanan
    Route::get('/reports/export-monthly-pdf/{year}/{month}', [ReportController::class, 'exportMonthlyPdf'])->name('reports.exportMonthlyPdf');

    // Manajemen User
    Route::get('users/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::resource('users', UserController::class)->withTrashed();
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Manajemen Hak Akses
    Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::get('role-permissions/{role}/edit', [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
    Route::put('role-permissions/{role}', [RolePermissionController::class, 'update'])->name('role-permissions.update');
    Route::post('role-permissions/{role}/copy', [RolePermissionController::class, 'copy'])->name('role-permissions.copy');

    // Manajemen Role
    Route::get('roles/archive', [RoleController::class, 'archive'])->name('roles.archive');
    Route::resource('roles', RoleController::class)->except(['index', 'show']);
    Route::post('roles/{role}/restore', [RoleController::class, 'restore'])->name('roles.restore')->withTrashed();

    // Announcements
    Route::get('announcements/archive', [App\Http\Controllers\AnnouncementController::class, 'archive'])->name('announcements.archive');
    Route::resource('announcements', 'App\Http\Controllers\AnnouncementController')->withTrashed();
    Route::post('announcements/{id}/restore', [App\Http\Controllers\AnnouncementController::class, 'restore'])->name('announcements.restore');
    Route::delete('announcements/{id}/force-delete', [App\Http\Controllers\AnnouncementController::class, 'forceDelete'])->name('announcements.forceDelete');

    // Absensi
    Route::get('/attendances/export', [AttendanceController::class, 'showExportForm'])->name('attendances.export');
    Route::get('/attendances/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.exportPdf');
    Route::resource('attendances', AttendanceController::class);

    // Izin
    Route::resource('leave-requests', \App\Http\Controllers\LeaveRequestController::class);
    Route::post('leave-requests/{leave_request}/approve', [\App\Http\Controllers\LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [\App\Http\Controllers\LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::get('leave-requests/{leave_request}/export-pdf', [\App\Http\Controllers\LeaveRequestController::class, 'exportPdf'])->name('leave-requests.exportPdf');

    // Phone Numbers
    Route::get('/phone-numbers', [\App\Http\Controllers\PhoneNumberController::class, 'index'])->name('phone-numbers.index');

    // Location & Media Settings
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/settings/location', [SettingController::class, 'locationSettings'])->name('settings.location');
        Route::post('/settings/location', [SettingController::class, 'updateLocationSettings'])->name('settings.location.update');
        Route::get('/settings/media', [SettingController::class, 'mediaSettings'])->name('settings.media');
        Route::post('/settings/media', [SettingController::class, 'updateMediaSettings'])->name('settings.media.update');

        // Manajemen Media (Manual Delete)
        Route::get('/media-management', [\App\Http\Controllers\MediaManagementController::class, 'index'])->name('media.index');
        Route::post('/media-management/delete-reports', [\App\Http\Controllers\MediaManagementController::class, 'deleteReports'])->name('media.deleteReports');
        Route::post('/media-management/delete-attendance', [\App\Http\Controllers\MediaManagementController::class, 'deleteAttendance'])->name('media.deleteAttendance');
    });
});

require __DIR__ . '/auth.php';



