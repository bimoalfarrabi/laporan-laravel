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
        $this->authorize('viewAny', LaporanHarianJaga::class); // Otorisasi untuk melihat daftar laporan

        // Logika filter data tetap di sini sesuai peran
        if (Auth::user()->role === 'danru' || Auth::user()->role === 'superadmin') {
        $laporan = LaporanHarianJaga::latest()->get(); // Danru/SuperAdmin melihat semua
        } else {
            $laporan = LaporanHarianJaga::where('user_id', Auth::id())->latest()->get(); // Anggota hanya melihat miliknya
        }

        return view('laporan-harian-jaga.index', compact('laporan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', LaporanHarianJaga::class); // Otorisasi untuk membuat laporan
        return view('laporan-harian-jaga.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', LaporanHarianJaga::class); // Otorisasi untuk menyimpan laporan

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
        $this->authorize('view', $laporanHarianJaga); // Otorisasi untuk melihat laporan spesifik
        return view('laporan-harian-jaga.show', compact('laporanHarianJaga'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaporanHarianJaga $laporanHarianJaga)
    {
        $this->authorize('update', $laporanHarianJaga); // Otorisasi untuk mengedit laporan
        return view('laporan-harian-jaga.edit', compact('laporanHarianJaga'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaporanHarianJaga $laporanHarianJaga)
    {
        $this->authorize('update', $laporanHarianJaga); // Otorisasi untuk memperbarui laporan

        $request->validate([
            'tanggal_jaga' => 'required|date',
            'shift' => 'required|string|max:255',
            'cuaca' => 'nullable|string|max:255',
            'kejadian_menonjol' => 'nullable|string',
            'catatan_serah_terima' => 'nullable|string',
            'status' => 'nullable|string|in:draft,submitted,approved,rejected',  // hanya nilai tertentu yang diizinkan
        ]);

        $laporanHarianJaga->tanggal_jaga = $request->tanggal_jaga;
        $laporanHarianJaga->shift = $request->shift;
        $laporanHarianJaga->cuaca = $request->cuaca;
        $laporanHarianJaga->kejadian_menonjol = $request->kejadian_menonjol;
        $laporanHarianJaga->catatan_serah_terima = $request->catatan_serah_terima;
        $laporanHarianJaga->status = $request->status ?? $laporanHarianJaga->status;
        $laporanHarianJaga->last_edited_by_user_id = Auth::id(); // Catat siapa yang mengedit terakhir
        $laporanHarianJaga->save();

        return redirect()->route('laporan-harian-jaga.index')->with('success', 'Laporan harian jaga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaporanHarianJaga $laporanHarianJaga)
    {
        $this->authorize('delete', $laporanHarianJaga); // Otorisasi untuk menghapus laporan

        $laporanHarianJaga->deleted_by_user_id = Auth::id(); // Catat siapa yang menghapus
        $laporanHarianJaga->save(); // simpan perubahan sebelum menghapus
        $laporanHarianJaga->delete(); // soft delete

        return redirect()->route('laporan-harian-jaga.index')->with('success', 'Laporan harian jaga berhasil dihapus.');
    }
}
