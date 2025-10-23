<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarianJaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanHarianJagaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laporan = LaporanHarianJaga::where('user_id', Auth::id())->latest()->get();
        return view('laporan-harian-jaga.index', compact('laporan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('laporan-harian-jaga.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validasi input
        $request->validate([
            'tanggal_jaga' => 'required|date',
            'shift' => 'required|string|max:255',
            'cuaca' => 'nullable|string|max:255',
            'kejadian_menonjol' => 'nullable|string',
            'catatan_serah_terima' => 'nullable|string',
            'status' => 'nullable|string|in:draft,submitted,approved,rejected',  // hanya nilai tertentu yang diizinkan
        ]);

        $laporan = new LaporanHarianJaga();
        $laporan->user_id = Auth::id();
        $laporan->tanggal_jaga = $request->tanggal_jaga;
        $laporan->shift = $request->shift;
        $laporan->cuaca = $request->cuaca;
        $laporan->kejadian_menonjol = $request->kejadian_menonjol;
        $laporan->catatan_serah_terima = $request->catatan_serah_terima;
        $laporan->status = $request->status ?? 'draft';  // default ke draft jika tidak diisi
        $laporan->save();

        return redirect()->route('laporan-harian-jaga.index')->with('success', 'Laporan harian jaga berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LaporanHarianJaga $laporanHarianJaga)
    {
        // Menampilkan detail laporan harian jaga
        if (Auth::user()->role === 'anggota' && $laporanHarianJaga->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('laporan-harian-jaga.show', compact('laporanHarianJaga'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaporanHarianJaga $laporanHarianJaga)
    {
        // Menampilkan form edit laporan harian jaga
        if (Auth::user()->role === 'anggota' && $laporanHarianJaga->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('laporan-harian-jaga.edit', compact('laporanHarianJaga'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaporanHarianJaga $laporanHarianJaga)
    {
        $request->validate([
            'tanggal_jaga' => 'required|date',
            'shift' => 'required|string|max:255',
            'cuaca' => 'nullable|string|max:255',
            'kejadian_menonjol' => 'nullable|string',
            'catatan_serah_terima' => 'nullable|string',
            'status' => 'nullable|string|in:draft,submitted,approved,rejected',  // hanya nilai tertentu yang diizinkan
        ]);

        // memperbarui laporan
        if (Auth::user()->role === 'anggota' && $laporanHarianJaga->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $laporanHarianJaga->tanggal_jaga = $request->tanggal_jaga;
        $laporanHarianJaga->shift = $request->shift;
        $laporanHarianJaga->cuaca = $request->cuaca;
        $laporanHarianJaga->kejadian_menonjol = $request->kejadian_menonjol;
        $laporanHarianJaga->catatan_serah_terima = $request->catatan_serah_terima;
        $laporanHarianJaga->status = $request->status ?? $laporanHarianJaga->status;
        $laporanHarianJaga->save();

        return redirect()->route('laporan-harian-jaga.index')->with('success', 'Laporan harian jaga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaporanHarianJaga $laporanHarianJaga)
    {
        // menghapus laporan
        if (Auth::user()->role === 'anggota' && $laporanHarianJaga->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $laporanHarianJaga->delete();

        return redirect()->route('laporan-harian-jaga.index')->with('success', 'Laporan harian jaga berhasil dihapus.');
    }
}
