<?php

use App\Http\Controllers\LaporanHarianJagaController;
use App\Http\Controllers\ProfileController;
use App\Models\LaporanHarianJaga;
use Illuminate\Support\Facades\Route;

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

    Route::get('/laporan-harian-jaga/arsip', [LaporanHarianJagaController::class, 'archive'])->name('laporan-harian-jaga.archive');
    Route::resource('laporan-harian-jaga', LaporanHarianJagaController::class)->withTrashed();

    Route::post('/laporan-harian-jaga/{id}/restore', [LaporanHarianJagaController::class, 'restore'])->name('laporan-harian-jaga.restore');
    Route::delete('/laporan-harian-jaga/{id}/force-delete', [LaporanHarianJagaController::class, 'forceDelete'])->name('laporan-harian-jaga.forceDelete');
});

require __DIR__.'/auth.php';
