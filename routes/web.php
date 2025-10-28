<?php

use App\Http\Controllers\LaporanHarianJagaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForcePasswordChangeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// perubahan password (diluar grup auth)
Route::get('/force-password-change', [ForcePasswordChangeController::class, 'showChangeForm'])->name('password.force-change');
Route::post('/force-password-change', [ForcePasswordChangeController::class, 'updatePassword'])->name('password.update-forced');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Laporan Harian Jaga
    Route::get('/laporan-harian-jaga/arsip', [LaporanHarianJagaController::class, 'archive'])->name('laporan-harian-jaga.archive');
    Route::resource('laporan-harian-jaga', LaporanHarianJagaController::class)->withTrashed();
    Route::post('/laporan-harian-jaga/{id}/restore', [LaporanHarianJagaController::class, 'restore'])->name('laporan-harian-jaga.restore');
    Route::delete('/laporan-harian-jaga/{id}/force-delete', [LaporanHarianJagaController::class, 'forceDelete'])->name('laporan-harian-jaga.forceDelete');

    // Manajemen Jenis Laporan
    Route::resource('report-types', ReportTypeController::class);

    // Laporan
    Route::get('/reports/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::resource('reports', ReportController::class)->withTrashed();
    Route::post('/reports/{id}/restore', [ReportController::class, 'restore'])->name('reports.restore');
    Route::delete('/reports/{id}/force-delete', [ReportController::class, 'forceDelete'])->name('reports.forceDelete');

    // Rute untuk approve dan reject laporan
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

    // Manajemen User
    Route::get('users/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::resource('users', UserController::class)->withTrashed();
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
});

require __DIR__ . '/auth.php';
