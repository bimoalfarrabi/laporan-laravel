<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportTypeController extends Controller
{
    public function explanation()
    {
        return view('report-types.explanation');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', ReportType::class); // Otorisasi untuk melihat daftar jenis laporan
        $reportTypes = ReportType::latest()->get();
        return view('report-types.index', compact('reportTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ReportType::class); // Otorisasi untuk membuat jenis laporan
        return view('report-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ReportType::class); // Otorisasi untuk menyimpan jenis laporan

        $request->validate([
            'name' => 'required|string|max:255|unique:report_types,name',
            'description' => 'nullable|string',
            'fields_schema' => 'required|json', // validasi json valid
            'is_active' => 'required|boolean',
        ]);

        $reportType = new ReportType();
        $reportType->name = $request->name;
        $reportType->slug = Str::slug($request->name);
        $reportType->description = $request->description;
        $reportType->fields_schema = json_decode($request->fields_schema, true); // simpan sebagai array
        $reportType->is_active = $request->boolean('is_active');
        $reportType->created_by_user_id = Auth::id();
        $reportType->updated_by_user_id = Auth::id();
        $reportType->save();

        return redirect()->route('report-types.index')->with('success', 'Report Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportType $reportType)
    {
        $this->authorize('view', ReportType::class); // Otorisasi untuk melihat jenis laporan
        return view('report-types.show', compact('reportType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportType $reportType)
    {
        $this->authorize('update', ReportType::class); // Otorisasi untuk mengedit jenis laporan
        return view('report-types.edit', compact('reportType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReportType $reportType)
    {
        $this->authorize('update', ReportType::class); // Otorisasi untuk memperbarui jenis laporan

        $request->validate([
            'name' => 'required|string|max:255|unique:report_types,name,' . $reportType->id,
            'description' => 'nullable|string',
            'fields_schema' => 'required|json', // validasi json valid
            'is_active' => 'required|boolean',
        ]);

        $reportType->name = $request->name;
        // Slug diperbarui hanya jika nama diubah
        $reportType->description = $request->description;
        $reportType->fields_schema = json_decode($request->fields_schema, true);
        $reportType->is_active = $request->boolean('is_active');
        $reportType->updated_by_user_id = Auth::id();
        $reportType->save();

        return redirect()->route('report-types.index')->with('success', 'Report Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportType $reportType)
    {
        $this->authorize('delete', ReportType::class); // Otorisasi untuk menghapus jenis laporan
        $reportType->delete();

        return redirect()->route('report-types.index')->with('success', 'Report Type deleted successfully.');
    }
}
