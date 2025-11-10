<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use App\Models\Role;
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
    public function index(Request $request)
    {
        $this->authorize('viewAny', ReportType::class); // Otorisasi untuk melihat daftar jenis laporan

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $reportTypes = ReportType::orderBy($sortBy, $sortDirection)->paginate(15);

        return view('report-types.index', compact('reportTypes', 'sortBy', 'sortDirection'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ReportType::class); // Otorisasi untuk membuat jenis laporan
        $fieldTypes = ['text', 'textarea', 'date', 'time', 'number', 'file', 'checkbox'];

        $defaultFields = [
            ['label' => 'Deskripsi', 'name' => 'deskripsi', 'type' => 'textarea', 'required' => true, 'order' => 1],
            ['label' => 'Tanggal', 'name' => 'tanggal', 'type' => 'date', 'required' => true, 'order' => 2],
            ['label' => 'Waktu', 'name' => 'waktu', 'type' => 'time', 'required' => true, 'order' => 3],
            ['label' => 'Upload Gambar', 'name' => 'upload_gambar', 'type' => 'file', 'required' => false, 'order' => 4],
        ];

        $roles = Role::all();
        return view('report-types.create', compact('fieldTypes', 'defaultFields', 'roles'));
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
            'is_active' => 'required|boolean',
            'fields' => 'array',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'fields.*.type' => 'required|string|in:text,textarea,date,time,number,file,checkbox,role_specific_text',
            'fields.*.required' => 'boolean',
            'fields.*.order' => 'required|integer',
            'fields.*.role_id' => 'nullable|exists:roles,id',
        ]);

        $reportType = new ReportType();
        $reportType->name = $request->name;
        $reportType->slug = Str::slug($request->name);
        $reportType->description = $request->description;
        $reportType->is_active = $request->boolean('is_active');
        $reportType->created_by_user_id = Auth::id();
        $reportType->updated_by_user_id = Auth::id();
        $reportType->save();

        foreach ($request->fields as $fieldData) {
            if ($fieldData['type'] !== 'role_specific_text') {
                $fieldData['role_id'] = null;
            }
            $reportType->reportTypeFields()->create($fieldData);
        }

        return redirect()->route('report-types.index')->with('success', 'Report Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportType $reportType)
    {
        $this->authorize('view', $reportType); // Otorisasi untuk melihat jenis laporan
        $reportType->load('reportTypeFields');
        return view('report-types.show', compact('reportType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportType $reportType)
    {
        $this->authorize('update', $reportType); // Otorisasi untuk mengedit jenis laporan
        $fieldTypes = ['text', 'textarea', 'date', 'time', 'number', 'file', 'checkbox'];
        $roles = Role::all();
        return view('report-types.edit', compact('reportType', 'fieldTypes', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReportType $reportType)
    {
        $this->authorize('update', $reportType); // Otorisasi untuk memperbarui jenis laporan

        $request->validate([
            'name' => 'required|string|max:255|unique:report_types,name,' . $reportType->id,
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'fields' => 'array',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.name' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'fields.*.type' => 'required|string|in:text,textarea,date,time,number,file,checkbox,role_specific_text',
            'fields.*.required' => 'boolean',
            'fields.*.order' => 'required|integer',
            'fields.*.role_id' => 'nullable|exists:roles,id',
        ]);

        $reportType->name = $request->name;
        $reportType->description = $request->description;
        $reportType->is_active = $request->boolean('is_active');
        $reportType->updated_by_user_id = Auth::id();
        $reportType->save();

        // Sync fields
        $existingFieldIds = $reportType->reportTypeFields->pluck('id')->toArray();
        $updatedFieldIds = [];

        foreach ($request->fields as $fieldData) {
            if ($fieldData['type'] !== 'role_specific_text') {
                $fieldData['role_id'] = null;
            }

            if (isset($fieldData['id'])) {
                // Update existing field
                $reportType->reportTypeFields()->where('id', $fieldData['id'])->update($fieldData);
                $updatedFieldIds[] = $fieldData['id'];
            } else {
                // Create new field
                $newField = $reportType->reportTypeFields()->create($fieldData);
                $updatedFieldIds[] = $newField->id;
            }
        }

        // Delete fields that were removed from the form
        $fieldsToDelete = array_diff($existingFieldIds, $updatedFieldIds);
        $reportType->reportTypeFields()->whereIn('id', $fieldsToDelete)->delete();

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
