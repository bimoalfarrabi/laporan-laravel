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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewReportNotification;
use App\Notifications\ReportStatusNotification;
use App\Models\User;

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
        // Eager load user roles to prevent N+1
        $query = Report::query()->with(['reportType', 'user.roles']);

        if (Auth::user()->hasRole('superadmin')) {
            // SuperAdmin can see all reports
        } elseif (Auth::user()->hasRole('manajemen')) {
            $query->whereHas('user.roles', fn($q) => $q->whereIn('name', ['danru', 'anggota']));
        } elseif (Auth::user()->hasRole('danru')) {
            $query->whereHas('user.roles', fn($q) => $q->whereIn('name', ['anggota', 'danru', 'backup']));
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
        switch ($sortBy) {
            case 'report_type_name':
                $query
                    ->join('report_types', 'reports.report_type_id', '=', 'report_types.id')
                    ->orderBy('report_types.name', $sortDirection)
                    ->select('reports.*');
                break;
            case 'user_name':
                $query
                    ->join('users', 'reports.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortDirection)
                    ->select('reports.*');
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
                break;
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
        $this->authorize('create', Report::class);

        $reportType = ReportType::with('reportTypeFields')->findOrFail($request->report_type_id);

        return DB::transaction(function () use ($request, $reportType) {
            try {
                $reportData = $this->processReportFields($request, $reportType);

                $report = new Report();
                $report->report_type_id = $reportType->id;
                $report->user_id = Auth::id();
                $report->data = $reportData;
                $report->status = 'belum disetujui';
                $report->last_edited_by_user_id = Auth::id();
                $report->save();

                // Notify Danru
                $danruUsers = User::role('danru')->get();
                foreach ($danruUsers as $danru) {
                    $danru->notify(new NewReportNotification($report));
                }

                return redirect()->route('reports.index')->with('success', 'Laporan berhasil dibuat.');
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('Error storing report: ' . $e->getMessage());
                // If it's a file storage error, we might want to show a specific message
                return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.')->withInput();
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report, Request $request)
    {
        // mengambil laporan, termasuk yg sudah dihapus secara soft delete
        // Eager load all necessary relationships to avoid N+1
        $report = Report::withTrashed()
            ->with([
                'reportType',
                'user.roles',
                'lastEditedBy.roles',
                'deletedBy',
                'approvedBy.roles',
                'rejectedBy'
            ])
            ->findOrFail($report->id);

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
        $this->authorize('update', $report);

        $reportType = ReportType::with('reportTypeFields')->findOrFail($report->report_type_id);

        return DB::transaction(function () use ($request, $report, $reportType) {
            try {
                $reportData = $this->processReportFields($request, $reportType, $report->data);

                $report->data = $reportData;
                $report->last_edited_by_user_id = Auth::id();
                $report->save();

                return redirect()->route('reports.index')->with('success', 'Laporan berhasil diperbarui.');
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('Error updating report: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui laporan. Silakan coba lagi.')->withInput();
            }
        });
    }

    /**
     * Process report fields for store and update methods.
     *
     * @param Request $request
     * @param ReportType $reportType
     * @param array $currentData Existing data for update scenarios
     * @return array
     * @throws ValidationException
     */
    private function processReportFields(Request $request, ReportType $reportType, array $currentData = []): array
    {
        $validationRules = [];
        $reportData = $currentData;

        // 1. Build Validation Rules
        foreach ($reportType->reportTypeFields as $field) {
            $fieldName = $field->name;
            $rules = [];

            if ($field->required) {
                if ($field->type === 'file' || $field->type === 'video') {
                    // For files/videos, check if we have existing files or new uploads
                    $existingFiles = $reportData[$fieldName] ?? [];
                    if (is_string($existingFiles)) {
                        $existingFiles = [$existingFiles];
                    }

                    $filesToDelete = $request->input('delete_' . $fieldName, []);

                    // If it's a video, existingFiles might be a single string path or null
                    if ($field->type === 'video' && !is_array($existingFiles) && $existingFiles) {
                        $existingFiles = [$existingFiles];
                    }

                    $remainingFilesCount = count($existingFiles) - count($filesToDelete);

                    if ($remainingFilesCount <= 0 && !$request->hasFile($fieldName)) {
                        $rules[] = 'required';
                    } else {
                        $rules[] = 'nullable';
                    }
                } else {
                    $rules[] = 'required';
                }
            } else {
                $rules[] = 'nullable';
            }

            if ($field->type === 'date') {
                $rules[] = 'date';
            } elseif ($field->type === 'time') {
                $rules[] = 'date_format:H:i';
            } elseif ($field->type === 'number') {
                $rules[] = 'numeric';
            } elseif ($field->type === 'file') {
                $rules[] = 'array';
                $rules[] = 'max:3';
                $validationRules[$fieldName . '.*'] = 'file|mimes:jpg,jpeg,png';
            } elseif ($field->type === 'video') {
                $rules[] = 'file';
                $rules[] = 'mimes:mp4,mov,avi,mkv,webm';
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        // 2. Custom Error Messages & Attributes
        $messages = [
            'required' => ':attribute wajib diisi.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'date_format' => ':attribute harus berformat waktu yang benar (HH:MM).',
            'numeric' => ':attribute harus berupa angka.',
            'array' => ':attribute harus berupa data yang valid.',
            'max.array' => 'Maksimal :max file yang diperbolehkan untuk :attribute.',
            'mimes' => ':attribute harus berupa file bertipe: :values.',
            'file' => ':attribute harus berupa file yang valid.',
            'max.file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobytes.',
        ];

        $customAttributes = [];
        foreach ($reportType->reportTypeFields as $field) {
            $customAttributes[$field->name] = $field->label;
        }

        $validator = Validator::make($request->all(), $validationRules, $messages, $customAttributes);

        // 3. Additional Validation (e.g., Max 3 files total)
        $validator->after(function ($validator) use ($request, $reportData, $reportType) {
            foreach ($reportType->reportTypeFields as $field) {
                if ($field->type === 'file') {
                    $fieldName = $field->name;
                    $existingFiles = $reportData[$fieldName] ?? [];
                    if (is_string($existingFiles))
                        $existingFiles = [$existingFiles];

                    $filesToDelete = $request->input('delete_' . $fieldName, []);
                    $newFiles = $request->file($fieldName, []);

                    // Ensure newFiles is an array
                    if (!is_array($newFiles)) {
                        $newFiles = [$newFiles];
                    }

                    $totalFiles = count($existingFiles) - count($filesToDelete) + count($newFiles);

                    if ($totalFiles > 3) {
                        $validator->errors()->add($fieldName, 'Maksimal total 3 gambar diperbolehkan.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 4. Process Data & Files
        foreach ($reportType->reportTypeFields as $field) {
            // Skip role-specific fields if user doesn't have the role
            if ($field->type === 'role_specific_text' && $field->role_id) {
                if (!Auth::user()->hasRole(Role::find($field->role_id)->name)) {
                    continue;
                }
            }

            $fieldName = $field->name;

            if ($field->type === 'file') {
                $existingFiles = $reportData[$fieldName] ?? [];
                if (is_string($existingFiles))
                    $existingFiles = [$existingFiles];

                $filesToDelete = $request->input('delete_' . $fieldName, []);

                $updatedFiles = [];
                // Handle Deletions
                foreach ($existingFiles as $path) {
                    if (in_array($path, $filesToDelete)) {
                        try {
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            } elseif (Storage::disk('nextcloud')->exists($path)) {
                                Storage::disk('nextcloud')->delete($path);
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to delete file: $path. Error: " . $e->getMessage());
                            // Continue even if delete fails, to keep DB consistent with user intent
                        }
                    } else {
                        $updatedFiles[] = $path;
                    }
                }

                // Handle New Uploads
                if ($request->hasFile($fieldName)) {
                    $files = $request->file($fieldName);
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $updatedFiles[] = $this->compressAndStoreImage($file);
                        }
                    } else {
                        $updatedFiles[] = $this->compressAndStoreImage($files);
                    }
                }

                $reportData[$fieldName] = $updatedFiles;

            } elseif ($field->type === 'video') {
                $existingVideo = $reportData[$fieldName] ?? null;
                $videoToDelete = $request->input('delete_' . $fieldName);

                // Handle deletion of existing video
                if ($videoToDelete && $existingVideo) {
                    try {
                        if (Storage::disk('public')->exists($existingVideo)) {
                            Storage::disk('public')->delete($existingVideo);
                        } elseif (Storage::disk('nextcloud')->exists($existingVideo)) {
                            Storage::disk('nextcloud')->delete($existingVideo);
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to delete video: $existingVideo. Error: " . $e->getMessage());
                    }
                    $reportData[$fieldName] = null; // Clear the field
                }

                // Handle new video upload
                if ($request->hasFile($fieldName)) {
                    // Delete old video if exists and not already deleted above
                    if ($existingVideo && !$videoToDelete) {
                        try {
                            if (Storage::disk('public')->exists($existingVideo)) {
                                Storage::disk('public')->delete($existingVideo);
                            } elseif (Storage::disk('nextcloud')->exists($existingVideo)) {
                                Storage::disk('nextcloud')->delete($existingVideo);
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to delete old video: $existingVideo. Error: " . $e->getMessage());
                        }
                    }
                    $reportData[$fieldName] = $this->storeVideo($request->file($fieldName));
                }
            } elseif ($field->type === 'checkbox') {
                $reportData[$fieldName] = $request->has($fieldName);
            } else {
                $reportData[$fieldName] = $request->input($fieldName);
            }
        }

        return $reportData;
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
        $report->save();

        // Notify the user who created the report
        $report->user->notify(new ReportStatusNotification($report));
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
            $report->save();

            // Notify the user who created the report
            $report->user->notify(new ReportStatusNotification($report));
            $report->rejected_by_user_id = Auth::id();
            $report->rejected_at = now();
            $report->save();
            return redirect()->back()->with('success', 'Laporan ditolak.');
        }

        abort(403, 'Anda tidak memiliki izin untuk menolak laporan ini.');
    }

    public function exportPdf(Report $report)
    {
        $this->authorize('exportPdf', $report);

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

    private function storeVideo($file): string
    {
        $accountName = Str::slug(Auth::user()->name);
        $timestamp = now()->format('YmdHis');
        $originalExtension = strtolower($file->getClientOriginalExtension());
        $filename = $accountName . '-' . $timestamp . '-' . uniqid() . '.' . $originalExtension;

        $year = now()->format('Y');
        $month = now()->format('m');
        $storagePath = 'satpam/reports/' . $year . '/' . $month;

        // Ensure directory exists
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath);
        }

        $path = $file->storeAs($storagePath, $filename, 'public');

        if ($path === false) {
            throw ValidationException::withMessages([
                'video' => 'Gagal mengunggah video. Silakan coba lagi.',
            ]);
        }

        return $path;
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
        $filename = $accountName . '-' . $timestamp . '-' . uniqid() . '.jpg';

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
                $filename = $accountName . '-' . $timestamp . '-' . uniqid() . '.png'; // Use PNG extension for PNG originals
                break;
            case 'gif':
                $imageResource = imagecreatefromgif($originalPath);
                break;
            default:
                // If file type is not supported, store it without compression
                $path = $file->storeAs('satpam/reports/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
                if ($path === false) {
                    throw ValidationException::withMessages([
                        'photo' => 'Gagal mengunggah foto (format tidak didukung). Silakan coba lagi.',
                    ]);
                }
                return $path;
        }

        if (!$imageResource) {
            // Fallback if image resource creation failed
            $path = $file->storeAs('satpam/reports/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
            if ($path === false) {
                throw ValidationException::withMessages([
                    'photo' => 'Gagal mengunggah foto (fallback). Silakan coba lagi.',
                ]);
            }
            return $path;
        }

        // Handle EXIF Rotation immediately after creating resource
        if (function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($originalPath);
                if ($exif && isset($exif['Orientation'])) {
                    $orientation = $exif['Orientation'];
                    switch ($orientation) {
                        case 3:
                            $imageResource = imagerotate($imageResource, 180, 0);
                            break;
                        case 6:
                            $imageResource = imagerotate($imageResource, -90, 0);
                            break;
                        case 8:
                            $imageResource = imagerotate($imageResource, 90, 0);
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Ignore EXIF errors
            }
        }

        $originalWidth = imagesx($imageResource);
        $originalHeight = imagesy($imageResource);

        $maxWidth = 1280;
        $maxHeight = 1280;

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
            0,
            0,
            0,
            0,
            (int) $newWidth,
            (int) $newHeight,
            $originalWidth,
            $originalHeight
        );

        // Define storage path
        $year = now()->format('Y');
        $month = now()->format('m');
        $storagePath = 'satpam/reports/' . $year . '/' . $month . '/' . $filename;
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

        // Ensure directory exists before upload
        $directoryPath = 'satpam/reports/' . $year . '/' . $month;
        if (!Storage::disk('public')->exists($directoryPath)) {
            Storage::disk('public')->makeDirectory($directoryPath);
        }

        // Upload to Public Disk
        $fileHandle = fopen($tempPath, 'r');
        $uploadResult = Storage::disk('public')->put($storagePath, $fileHandle);
        fclose($fileHandle);

        // Clean up temp file
        unlink($tempPath);

        // Free up memory
        imagedestroy($imageResource);
        imagedestroy($newImageResource);

        if (!$uploadResult) {
            throw ValidationException::withMessages([
                'photo' => 'Gagal mengunggah foto. Silakan coba lagi.',
            ]);
        }

        return $storagePath;
    }

    /**
     * Rotate an image permanently.
     */
    public function rotateImage(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'image_path' => 'required|string',
            'angle' => 'required|integer|in:90,-90,180',
        ]);

        $imagePath = $request->input('image_path');
        $angle = $request->input('angle');

        // Verify that the image belongs to the report
        $found = false;
        foreach ($report->data as $key => $value) {
            if (is_array($value)) {
                if (in_array($imagePath, $value)) {
                    $found = true;
                    break;
                }
            } elseif ($value === $imagePath) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json(['message' => 'Image not found in report.'], 404);
        }

        if (Storage::disk('public')->exists($imagePath)) {
            $fileContent = Storage::disk('public')->get($imagePath);
        } elseif (Storage::disk('nextcloud')->exists($imagePath)) {
            $fileContent = Storage::disk('nextcloud')->get($imagePath);
        } else {
            return response()->json(['message' => 'File not found.'], 404);
        }

        // Create temp file
        $tempPath = tempnam(sys_get_temp_dir(), 'rotate_image_');
        file_put_contents($tempPath, $fileContent);

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $imageResource = null;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $imageResource = imagecreatefromjpeg($tempPath);
                break;
            case 'png':
                $imageResource = imagecreatefrompng($tempPath);
                imagealphablending($imageResource, true);
                imagesavealpha($imageResource, true);
                break;
            default:
                unlink($tempPath);
                return response()->json(['message' => 'Unsupported image type.'], 400);
        }

        if (!$imageResource) {
            unlink($tempPath);
            return response()->json(['message' => 'Failed to load image.'], 500);
        }

        // Calculate rotation angle
        $rotationAngle = 0;
        if ($angle == 90) {
            $rotationAngle = 270;
        } elseif ($angle == -90) {
            $rotationAngle = 90;
        } elseif ($angle == 180) {
            $rotationAngle = 180;
        }

        $rotatedImage = imagerotate($imageResource, $rotationAngle, 0);

        // Save back to temp path
        if ($extension === 'png') {
            imagealphablending($rotatedImage, false);
            imagesavealpha($rotatedImage, true);
            imagepng($rotatedImage, $tempPath);
        } else {
            imagejpeg($rotatedImage, $tempPath, 90);
        }

        // Upload back to Nextcloud
        // Upload back to storage
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->put($imagePath, fopen($tempPath, 'r'));
        } elseif (Storage::disk('nextcloud')->exists($imagePath)) {
            Storage::disk('nextcloud')->put($imagePath, fopen($tempPath, 'r'));
        }

        imagedestroy($imageResource);
        imagedestroy($rotatedImage);
        unlink($tempPath);

        return response()->json(['message' => 'Image rotated successfully.']);
    }
}
