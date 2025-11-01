<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class); // Otorisasi untuk melihat daftar laporan

        $search = $request->query('search');
        $filterReportTypeId = $request->query('report_type_id');

        $query = Report::query()->with('reportType', 'user');

        if (Auth::user()->hasRole('superadmin')) {
            // SuperAdmin can see all reports
        } elseif (Auth::user()->hasRole('danru')) {
            // Danru only sees reports from their shift
            $danruShift = Auth::user()->shift;
            $query->whereHas('user', function ($q) use ($danruShift) {
                $q->where('shift', $danruShift);
            });
        } else {
            // Anggota only sees their own reports
            $query->where('user_id', Auth::id());
        }

        if ($search) {
            if (Auth::user()->hasRole('anggota')) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('reportType', function ($qr) use ($search) {
                        $qr->where('name', 'like', '%' . $search . '%');
                    });
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('reportType', function ($qr) use ($search) {
                        $qr->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('user', function ($qr) use ($search) {
                        $qr->where('name', 'like', '%' . $search . '%');
                    });
                });
            }
        }

        if ($filterReportTypeId) {
            $query->where('report_type_id', $filterReportTypeId);
        }

        $reports = $query->latest()->get();
        $reportTypes = ReportType::where('is_active', true)->get(); // Untuk filter dropdown

        return view('reports.index', compact('reports', 'search', 'filterReportTypeId', 'reportTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Report::class); // Otorisasi untuk membuat laporan

        $reportTypeId = $request->query('report_type_id');

        if (!$reportTypeId) {
            // jika tidak ada report_type_id di query, tampilkan pilihan jenis laporan
            $reportTypes = ReportType::where('is_active', true)->get();
            return view('reports.select-report-type', compact('reportTypes'));
        }

        $reportType = ReportType::findOrFail($reportTypeId);
        return view('reports.create', compact('reportType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Report::class); // Otorisasi untuk menyimpan laporan

        $reportType = ReportType::with('reportTypeFields')->findOrFail($request->report_type_id);

        // bangun aturan validasi dinamis berdasarkan reportTypeFields
        $validationRules = [];
        $reportData = []; // untuk menyimpan data laporan

        foreach ($reportType->reportTypeFields as $field) {
            $fieldName = $field->name;
            $rules = [];

            if ($field->required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }
            // tambahkan validasi tipe data jika diperlukan
            if ($field->type === 'date') {
                $rules[] = 'date';
            } elseif ($field->type === 'time') {
                $rules[] = 'date_format:H:i';
            } elseif ($field->type === 'number') {
                $rules[] = 'numeric';
            } elseif ($field->type === 'file') {
                $rules[] = 'file';
                $rules[] = 'mimes:jpg,jpeg,png'; // hanya file gambar
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // proses upload file dan siapkan data
        foreach ($reportType->reportTypeFields as $field) {
            $fieldName = $field->name;
            if ($field->type === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);

                // --- Native GD Compression Logic ---
                $originalPath = $file->getRealPath();
                $originalExtension = strtolower($file->getClientOriginalExtension());

                // Generate unique filename with .jpg extension
                $accountName = Str::slug(Auth::user()->name);
                $timestamp = now()->format('YmdHis');
                $filename = $accountName . '-' . $timestamp . '.jpg';

                // Create image resource from uploaded file
                $imageResource = null;
                switch ($originalExtension) {
                    case 'jpg':
                    case 'jpeg':
                        $imageResource = imagecreatefromjpeg($originalPath);
                        break;
                    case 'png':
                        $imageResource = imagecreatefrompng($originalPath);
                        break;
                    case 'gif':
                        $imageResource = imagecreatefromgif($originalPath);
                        break;
                }

                if ($imageResource) {
                    $year = now()->format('Y');
                    $month = now()->format('m');
                    $storagePath = 'reports/' . $year . '/' . $month . '/' . $filename;
                    $publicPath = storage_path('app/public/' . $storagePath);

                    // Ensure directory exists
                    if (!file_exists(dirname($publicPath))) {
                        mkdir(dirname($publicPath), 0755, true);
                    }

                    $quality = 90; // Start with high quality
                    $maxFileSize = 1024 * 1024; // 1MB in bytes
                    $tempPath = tempnam(sys_get_temp_dir(), 'compressed_image_'); // Temporary file for compression

                    do {
                        // Save the image with current quality to a temporary file
                        imagejpeg($imageResource, $tempPath, $quality);
                        $fileSize = filesize($tempPath);

                        if ($fileSize > $maxFileSize && $quality > 10) {
                            $quality -= 5; // Reduce quality
                        } else {
                            break; // Exit loop if size is acceptable or quality is too low
                        }
                    } while ($quality >= 10);

                    // Move the compressed image from temporary path to public storage
                    rename($tempPath, $publicPath);

                    // Free up memory
                    imagedestroy($imageResource);

                    $reportData[$fieldName] = $storagePath;
                } else {
                    // If file type is not supported, store it without compression
                    $reportData[$fieldName] = $file->store('reports/' . Auth::id(), 'public');
                }
                // --- End of Native GD Logic ---

            } elseif ($field['type'] === 'checkbox') {
                $reportData[$fieldName] = $request->has($fieldName); // simpan true/false
            } else {
                $reportData[$fieldName] = $request->input($fieldName);
            }
        }

        $report = new Report();
        $report->report_type_id = $reportType->id;
        $report->user_id = Auth::id();
        $report->data = $reportData; // simpan data
        $report->status = 'belum disetujui'; // default status
        $report->last_edited_by_user_id = Auth::id();
        $report->save();

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        // mengambil laporan, termasuk yg sudah dihapus secara soft delete
        $report = Report::withTrashed()->with('reportType', 'user', 'lastEditedBy', 'deletedBy')->findOrFail($report->id);

        $this->authorize('view', $report); // Otorisasi untuk melihat laporan spesifik
        return view('reports.show', compact('report'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $this->authorize('update', $report); // Otorisasi untuk mengedit laporan
        return view('reports.edit', compact('report'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report); // Otorisasi untuk memperbarui laporan

        $reportType = ReportType::with('reportTypeFields')->findOrFail($report->report_type_id);

        $validationRules = [];
        $reportData = $report->data; // untuk menyimpan data laporan

        // bangun aturan validasi dinamis berdasarkan reportTypeFields
        foreach ($reportType->reportTypeFields as $field) {
            $fieldName = $field->name;
            $rules = [];

            if ($field->required) {
                // jika required dan type file, hanya required jika tidak ada file lama
                if ($field->type === 'file' && !isset($reportData[$fieldName])) {
                    $rules[] = 'required';
                } elseif ($field->type !== 'file') {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }
            }

            if ($field->type === 'date') {
                $rules[] = 'date';
            } elseif ($field->type === 'time') {
                $rules[] = 'date_format:H:i';
            } elseif ($field->type === 'number') {
                $rules[] = 'numeric';
            } elseif ($field->type === 'file') {
                $rules[] = 'file';
                $rules[] = 'mimes:jpg,jpeg,png'; // hanya file gambar
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // proses upload file dan upload data
        foreach ($reportType->reportTypeFields as $field) {
            $fieldName = $field->name;
                        if ($field->type === 'file') {
                            if ($request->hasFile($fieldName)) {
                                // hapus file lama jika ada
                                if (isset($reportData[$fieldName])) {
                                    Storage::disk('public')->delete($reportData[$fieldName]);
                                }

                                $file = $request->file($fieldName);

                                // --- Native GD Compression Logic ---
                                $originalPath = $file->getRealPath();
                                $originalExtension = strtolower($file->getClientOriginalExtension());

                                // Generate unique filename with .jpg extension
                                $accountName = Str::slug(Auth::user()->name);
                                $timestamp = now()->format('YmdHis');
                                $filename = $accountName . '-' . $timestamp . '.jpg';

                                // Create image resource from uploaded file
                                $imageResource = null;
                                switch ($originalExtension) {
                                    case 'jpg':
                                    case 'jpeg':
                                        $imageResource = imagecreatefromjpeg($originalPath);
                                        break;
                                    case 'png':
                                        $imageResource = imagecreatefrompng($originalPath);
                                        break;
                                    case 'gif':
                                        $imageResource = imagecreatefromgif($originalPath);
                                        break;
                                }

                                if ($imageResource) {
                                    $year = now()->format('Y');
                                    $month = now()->format('m');
                                    $storagePath = 'reports/' . $year . '/' . $month . '/' . $filename;
                                    $publicPath = storage_path('app/public/' . $storagePath);

                                    // Ensure directory exists
                                    if (!file_exists(dirname($publicPath))) {
                                        mkdir(dirname($publicPath), 0755, true);
                                    }

                                    $quality = 90; // Start with high quality
                                    $maxFileSize = 1024 * 1024; // 1MB in bytes
                                    $tempPath = tempnam(sys_get_temp_dir(), 'compressed_image_'); // Temporary file for compression

                                    do {
                                        // Save the image with current quality to a temporary file
                                        imagejpeg($imageResource, $tempPath, $quality);
                                        $fileSize = filesize($tempPath);

                                        if ($fileSize > $maxFileSize && $quality > 10) {
                                            $quality -= 5; // Reduce quality
                                        } else {
                                            break; // Exit loop if size is acceptable or quality is too low
                                        }
                                    } while ($quality >= 10);

                                    // Move the compressed image from temporary path to public storage
                                    rename($tempPath, $publicPath);

                                    // Free up memory
                                    imagedestroy($imageResource);

                                    $reportData[$fieldName] = $storagePath;
                                } else {
                                    // If file type is not supported, store it without compression
                                    $reportData[$fieldName] = $file->store('reports/' . Auth::id(), 'public');
                                }
                                // --- End of Native GD Logic ---
                            }
                            // jika tidak ada file baru, biarkan file lama (jangan lakukan apa-apa)
                        } elseif ($field->type === 'checkbox') {
                $reportData[$fieldName] = $request->has($fieldName); // simpan true/false
            } else {
                $reportData[$fieldName] = $request->input($fieldName);
            }
        }

        $report->data = $reportData;
        $report->last_edited_by_user_id = Auth::id();
        $report->save();

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report); // Otorisasi untuk menghapus laporan

        $report->deleted_by_user_id = Auth::id(); // Catat siapa yang menghapus
        $report->save(); // simpan perubahan sebelum menghapus
        $report->delete(); // soft delete

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function archive()
    {
        $this->authorize('viewAny', Report::class); // Otorisasi untuk melihat arsip laporan

        if (Auth::user()->hasRole('anggota')) {
            abort(403, 'Anda tidak memiliki akses ke arsip laporan.'); // anggota tidak boleh mengakses arsip
        }

        $reports = Report::onlyTrashed()->with('reportType', 'user', 'deletedBy')->latest()->get(); // hanya mengambil yang dihapus secara soft delete

        return view('reports.archive', compact('reports'));
    }

    public function restore($id)
    {
        $report = Report::withTrashed()->findOrFail($id);
        $this->authorize('restore', $report); // Otorisasi untuk mengembalikan laporan

        $report->restore();

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $report = Report::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $report); // Otorisasi untuk menghapus permanen laporan

        $report->forceDelete();

        return redirect()->route('reports.archive')->with('success', 'Laporan berhasil dihapus secara permanen.');
    }

    public function approve(Report $report)
    {
        $this->authorize('approve', $report); // Menggunakan policy approve

        if (Auth::user()->hasRole('danru') || Auth::user()->hasRole('superadmin')) {
            $report->status = 'disetujui';
            $report->save();
            return redirect()->back()->with('success', 'Laporan disetujui.');
        }

        abort(403, 'Anda tidak memiliki izin untuk menyetujui laporan ini.');
    }

    public function reject(Report $report)
    {
        $this->authorize('reject', $report); // Menggunakan policy reject

        if (Auth::user()->hasRole('danru') || Auth::user()->hasRole('superadmin')) {
            $report->status = 'ditolak';
            $report->save();
            return redirect()->back()->with('success', 'Laporan ditolak.');
        }

        abort(403, 'Anda tidak memiliki izin untuk menolak laporan ini.');
    }

    public function exportPdf(Report $report)
    {
        $this->authorize('view', $report); // Use the existing view policy

        $report->load('reportType.reportTypeFields'); // Eager load fields

        $pdf = Pdf::loadView('reports.pdf', compact('report'));
        $accountName = Str::slug(Auth::user()->name);
        $reportTypeName = Str::slug($report->reportType->name);
        $timestamp = $report->created_at->format('YmdHis');
        $filename = $accountName . '-' . $reportTypeName . '-' . $timestamp . '.pdf';
        return $pdf->download($filename);
    }
}
