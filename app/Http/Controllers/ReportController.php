<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportType;
use App\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $search = $request->query('search');
        $filterReportTypeId = $request->query('report_type_id');
        $filterByUser = $request->query('filter_by_user');
        $filterByStatus = $request->query('filter_by_status');
        $filterDate = $request->query('filter_date');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        // Base query with role-based restrictions
        $query = Report::query()->with(['reportType', 'user']);

        if (Auth::user()->hasRole('superadmin')) {
            // SuperAdmin can see all reports
        } elseif (Auth::user()->hasRole('manajemen')) {
            $query->whereHas('user.roles', fn($q) => $q->whereIn('name', ['danru', 'anggota']));
        } elseif (Auth::user()->hasRole('danru')) {
            $query->whereHas('user.roles', fn($q) => $q->whereIn('name', ['anggota', 'danru']));
        } else {
            // Anggota can see all reports from other 'anggota'
            $query->whereHas('user.roles', fn($q) => $q->where('name', 'anggota'));
        }

        // Apply search and filter to the base query
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('reportType', fn($qr) => $qr->where('name', 'like', '%' . $search . '%'));
                if (!Auth::user()->hasRole('anggota')) {
                    $q->orWhereHas('user', fn($qr) => $qr->where('name', 'like', '%' . $search . '%'));
                }
            });
        }

        if ($filterReportTypeId) {
            $query->where('report_type_id', $filterReportTypeId);
        }

        if ($filterByUser) {
            $query->where('user_id', Auth::id());
        }

        if ($filterByStatus) {
            $query->where('status', $filterByStatus);
        }

        if ($filterDate) {
            $query->whereDate('created_at', $filterDate);
        }

        // Apply sorting
        if ($sortBy == 'report_type_name') {
            $query
                ->join('report_types', 'reports.report_type_id', '=', 'report_types.id')
                ->orderBy('report_types.name', $sortDirection)
                ->select('reports.*');
        } elseif ($sortBy == 'user_name') {
            $query
                ->join('users', 'reports.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortDirection)
                ->select('reports.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Paginate the results
        $reports = $query->paginate(15)->appends($request->query());

        $reportTypes = ReportType::where('is_active', true)->get();

        if ($request->ajax()) {
            return view('reports._results', compact(
                'reports',
                'sortBy',
                'sortDirection'
            ))->render();
        }

        return view('reports.index', compact(
            'reports',
            'search',
            'filterReportTypeId',
            'reportTypes',
            'sortBy',
            'sortDirection',
            'filterByUser',
            'filterByStatus',
            'filterDate'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Report::class);  // Otorisasi untuk membuat laporan

        $reportTypeId = $request->query('report_type_id');

        if (!$reportTypeId) {
            // jika tidak ada report_type_id di query, tampilkan pilihan jenis laporan
            $reportTypes = ReportType::where('is_active', true)->get();
            return view('reports.select-report-type', compact('reportTypes'));
        }

        $reportType = ReportType::findOrFail($reportTypeId);

        // Restrict 'Laporan Harian Jaga (LHJ) / Shift Report' to danru only
        if ($reportType->name === 'Laporan Harian Jaga (LHJ) / Shift Report' && !Auth::user()->hasRole('danru')) {
            return redirect()->route('reports.create')->with('error', 'Hanya Danru yang dapat membuat Laporan Harian Jaga.');
        }

        $reportType->reportTypeFields = $reportType->reportTypeFields->filter(function ($field) {
            if ($field->type === 'role_specific_text' && $field->role_id) {
                return Auth::user()->hasRole(Role::find($field->role_id)->name);
            }
            return true;
        });

        return view('reports.create', compact('reportType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Report::class);  // Otorisasi untuk menyimpan laporan

        $reportType = ReportType::with('reportTypeFields')->findOrFail($request->report_type_id);

        // bangun aturan validasi dinamis berdasarkan reportTypeFields
        $validationRules = [];
        $reportData = [];  // untuk menyimpan data laporan

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
                $rules[] = 'mimes:jpg,jpeg,png';  // hanya file gambar
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($reportType->reportTypeFields as $field) {
            if ($field->type === 'role_specific_text' && $field->role_id) {
                if (!Auth::user()->hasRole(Role::find($field->role_id)->name)) {
                    continue;  // skip this field if user does not have the role
                }
            }

            $fieldName = $field->name;
            if ($field->type === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $reportData[$fieldName] = $this->compressAndStoreImage($file);
            } elseif ($field['type'] === 'checkbox') {
                $reportData[$fieldName] = $request->has($fieldName);  // simpan true/false
            } else {
                $reportData[$fieldName] = $request->input($fieldName);
            }
        }

        $report = new Report();
        $report->report_type_id = $reportType->id;
        $report->user_id = Auth::id();
        $report->data = $reportData;  // simpan data
        $report->status = 'belum disetujui';  // default status
        $report->last_edited_by_user_id = Auth::id();

        // Automatically record danru's shift for LHJ reports
        // if ($reportType->name === 'Laporan Harian Jaga (LHJ) / Shift Report' && Auth::user()->hasRole('danru')) {
        //     $report->shift = Auth::user()->shift;
        // }
        $report->save();

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report, Request $request)
    {
        // mengambil laporan, termasuk yg sudah dihapus secara soft delete
        $report = Report::withTrashed()->with('reportType', 'user', 'lastEditedBy', 'deletedBy')->findOrFail($report->id);

        $this->authorize('view', $report);  // Otorisasi untuk melihat laporan spesifik

        // Fetch previous and next reports based on ID, excluding soft-deleted reports and reports from soft-deleted users
        $previousReport = Report::where('id', '<', $report->id)
            ->whereHas('user', function ($query) {  // Ensure user is not soft-deleted
                $query->whereNull('deleted_at');
            })
            ->orderBy('id', 'desc')
            ->first();

        $nextReport = Report::where('id', '>', $report->id)
            ->whereHas('user', function ($query) {  // Ensure user is not soft-deleted
                $query->whereNull('deleted_at');
            })
            ->orderBy('id', 'asc')
            ->first();

        // If it's an AJAX request, return only the report details partial
        if ($request->ajax()) {
            return view('reports.partials.report_details', compact('report', 'previousReport', 'nextReport'))->render();
        }

        return view('reports.show', compact('report', 'previousReport', 'nextReport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $this->authorize('update', $report);  // Otorisasi untuk mengedit laporan
        return view('reports.edit', compact('report'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);  // Otorisasi untuk memperbarui laporan

        $reportType = ReportType::with('reportTypeFields')->findOrFail($report->report_type_id);

        $validationRules = [];
        $reportData = $report->data;  // untuk menyimpan data laporan

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
                $rules[] = 'mimes:jpg,jpeg,png';  // hanya file gambar
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
                    $reportData[$fieldName] = $this->compressAndStoreImage($file);
                }
                // jika tidak ada file baru, biarkan file lama (jangan lakukan apa-apa)
            } elseif ($field->type === 'checkbox') {
                $reportData[$fieldName] = $request->has($fieldName);  // simpan true/false
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
        $this->authorize('delete', $report);  // Otorisasi untuk menghapus laporan

        $report->deleted_by_user_id = Auth::id();  // Catat siapa yang menghapus
        $report->save();  // simpan perubahan sebelum menghapus
        $report->delete();  // soft delete

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function archive()
    {
        $this->authorize('viewAny', Report::class);  // Otorisasi untuk melihat arsip laporan

        if (Auth::user()->hasRole('anggota')) {
            abort(403, 'Anda tidak memiliki akses ke arsip laporan.');  // anggota tidak boleh mengakses arsip
        }

        $reports = Report::onlyTrashed()->with('reportType', 'user', 'deletedBy')->latest()->get();  // hanya mengambil yang dihapus secara soft delete

        return view('reports.archive', compact('reports'));
    }

    public function restore($id)
    {
        $report = Report::withTrashed()->findOrFail($id);
        $this->authorize('restore', $report);  // Otorisasi untuk mengembalikan laporan

        $report->restore();

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $report = Report::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $report);  // Otorisasi untuk menghapus permanen laporan

        $report->forceDelete();

        return redirect()->route('reports.archive')->with('success', 'Laporan berhasil dihapus secara permanen.');
    }

    public function approve(Report $report)
    {
        $this->authorize('approve', $report);  // Menggunakan policy approve

        $report->status = 'disetujui';
        $report->approved_by_user_id = Auth::id();
        $report->approved_at = now();
        $report->save();
        return redirect()->back()->with('success', 'Laporan disetujui.');
    }

    public function reject(Report $report)
    {
        $this->authorize('reject', $report);  // Menggunakan policy reject

        if (Auth::user()->hasRole(['danru', 'superadmin', 'manajemen'])) {
            $report->status = 'ditolak';
            $report->rejected_by_user_id = Auth::id();
            $report->rejected_at = now();
            $report->save();
            return redirect()->back()->with('success', 'Laporan ditolak.');
        }

        abort(403, 'Anda tidak memiliki izin untuk menolak laporan ini.');
    }

    public function exportPdf(Report $report)
    {
        $this->authorize('view', $report);  // Use the existing view policy

        $report->load('reportType.reportTypeFields');  // Eager load fields

        $pdf = Pdf::loadView('reports.pdf', compact('report'));
        $accountName = Str::slug(Auth::user()->name);
        $reportTypeName = Str::slug($report->reportType->name);
        $timestamp = $report->created_at->format('YmdHis');
        $filename = $accountName . '-' . $reportTypeName . '-' . $timestamp . '.pdf';
        return $pdf->download($filename);
    }

    public function showExportForm()
    {
        $this->authorize('exportMonthly', Report::class);  // Re-use existing policy
        return view('reports.export');
    }

    public function exportMonthlyPdf(Request $request, $year, $month)
    {
        $this->authorize('exportMonthly', Report::class);  // New policy method for authorization

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $reports = Report::with('reportType', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('user', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'anggota');
                });
            })
            ->latest()
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan anggota yang ditemukan untuk bulan ini.');
        }

        $pdf = Pdf::loadView('reports.monthly-pdf', compact('reports', 'year', 'month'));
        $filename = 'Laporan_Anggota_Bulan_' . $month . '_' . $year . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Helper method to compress and store an image.
     *
     *
     *
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return string The path to the stored image.
     */
        private function compressAndStoreImage($file): string
        {
            $originalPath = $file->getRealPath();
            $originalExtension = strtolower($file->getClientOriginalExtension());
    
            // Generate unique filename with .jpg extension (default)
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
                    // Preserve transparency for PNG
                    imagealphablending($imageResource, true);
                    imagesavealpha($imageResource, true);
                    $filename = $accountName . '-' . $timestamp . '.png'; // Use PNG extension for PNG originals
                    break;
                case 'gif':
                    $imageResource = imagecreatefromgif($originalPath);
                    break;
                default:
                    // If file type is not supported, store it without compression
                    return $file->storeAs('reports/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
            }
    
            if (!$imageResource) {
                // Fallback if image resource creation failed
                return $file->storeAs('reports/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
            }
    
            $originalWidth = imagesx($imageResource);
            $originalHeight = imagesy($imageResource);
    
            $maxWidth = 1280; // Max width for images (reverted)
            $maxHeight = 1280; // Max height for images (reverted)
    
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
    
            // Resize if image is larger than max dimensions
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $ratio = $originalWidth / $originalHeight;
                if ($ratio > 1) { // Landscape
                    $newWidth = $maxWidth;
                    $newHeight = $maxWidth / $ratio;
                } else { // Portrait or Square
                    $newHeight = $maxHeight;
                    $newWidth = $maxHeight * $ratio;
                }
            }
    
            // Create a new true color image with the new dimensions
            $newImageResource = imagecreatetruecolor((int) $newWidth, (int) $newHeight);
    
            // Preserve transparency for PNG
            if ($originalExtension === 'png') {
                imagealphablending($newImageResource, false);
                imagesavealpha($newImageResource, true);
                $transparent = imagecolorallocatealpha($newImageResource, 255, 255, 255, 127);
                imagefilledrectangle($newImageResource, 0, 0, (int) $newWidth, (int) $newHeight, $transparent);
            }
    
            // Resample (resize) the image
            imagecopyresampled(
                $newImageResource,
                $imageResource,
                0, 0, 0, 0,
                (int) $newWidth, (int) $newHeight,
                $originalWidth, $originalHeight
            );
    
            // Define storage path
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
                if ($originalExtension === 'png') { // Save as PNG if original was PNG
                    imagepng($newImageResource, $tempPath, floor($quality / 10)); // PNG quality 0-9
                } else { // Otherwise, save as JPEG
                    imagejpeg($newImageResource, $tempPath, $quality);
                }
    
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
            imagedestroy($newImageResource);
    
            return $storagePath;
        }
}
