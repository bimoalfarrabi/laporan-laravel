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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// perubahan password (diluar grup auth)
Route::get('/force-password-change', [ForcePasswordChangeController::class, 'showChangeForm'])->name('password.force-change');
Route::post('/force-password-change', [ForcePasswordChangeController::class, 'updatePassword'])->name('password.update-forced');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Manajemen Jenis Laporan
    Route::get('report-types/explanation', [ReportTypeController::class, 'explanation'])->name('report-types.explanation');
    Route::resource('report-types', ReportTypeController::class);

    // Laporan
    Route::get('/reports/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::resource('reports', ReportController::class)->withTrashed();
    Route::post('/reports/{id}/restore', [ReportController::class, 'restore'])->name('reports.restore');
    Route::delete('/reports/{id}/force-delete', [ReportController::class, 'forceDelete'])->name('reports.forceDelete');

    // Rute untuk approve dan reject laporan
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

    // Rute untuk export PDF laporan
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
    Route::resource('attendances', AttendanceController::class);
});

require __DIR__ . '/auth.php';
