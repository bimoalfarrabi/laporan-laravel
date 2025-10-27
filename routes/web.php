<?php

use App\Http\Controllers\LaporanHarianJagaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
