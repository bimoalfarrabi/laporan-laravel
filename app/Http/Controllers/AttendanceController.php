<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:viewAny,App\Models\Attendance")->only("index");
        $this->middleware("can:create,App\Models\Attendance")->only([
            "create",
            "store",
        ]);
        $this->middleware("can:export,App\Models\Attendance")->only([
            "showExportForm",
            "exportPdf",
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Attendance::class);

        $user = Auth::user();
        $filterDate = $request->input("date")
            ? Carbon::parse($request->input("date"))
            : now();
        $search = $request->input("search");

        // Base queries
        $attendanceQuery = Attendance::with("user.roles")->whereDate(
            "time_in",
            $filterDate,
        );

        $leaveQuery = LeaveRequest::with("user.roles")
            ->where("status", "disetujui")
            ->where("start_date", "<=", $filterDate->format("Y-m-d"))
            ->where("end_date", ">=", $filterDate->format("Y-m-d"));

        // Apply filters
        $this->applyRoleBasedFilters($attendanceQuery, $leaveQuery, $user);
        $this->applySearchFilter($attendanceQuery, $leaveQuery, $search);

        $attendances = $attendanceQuery->get();
        $leaveRequests = $leaveQuery->get();

        // Attach leave requests to attendances
        $this->attachLeaveRequestsToAttendances($attendances, $leaveRequests);

        // Create virtual records and merge
        $usersOnLeave = $this->getVirtualLeaveRecords($leaveRequests, $filterDate);
        $combined = $this->mergeRecords($attendances, $usersOnLeave);

        // Sort and paginate
        $sortBy = $request->query("sort_by", "user.name");
        $sortDirection = $request->query("sort_direction", "asc");

        $sorted = $this->sortRecords($combined, $sortBy, $sortDirection);
        $paginatedItems = $this->paginateRecords($sorted, 15, $request);

        $viewData = [
            "attendances" => $paginatedItems,
            "sortBy" => $sortBy,
            "sortDirection" => $sortDirection,
        ];

        if ($request->ajax()) {
            return view("attendances._results", $viewData)->render();
        }

        return view("attendances.index", $viewData);
    }

    private function applyRoleBasedFilters($attendanceQuery, $leaveQuery, $user)
    {
        if ($user->hasRole(["anggota", "backup"])) {
            $attendanceQuery->where("user_id", $user->id);
            $leaveQuery->where("user_id", $user->id);
        } elseif ($user->hasRole("danru")) {
            $attendanceQuery->whereHas(
                "user.roles",
                fn($q) => $q->whereIn("name", ["anggota", "danru"]),
            );
            $leaveQuery->whereHas(
                "user.roles",
                fn($q) => $q->whereIn("name", ["anggota", "danru"]),
            );
        } elseif ($user->hasRole("manajemen")) {
            $attendanceQuery->whereHas(
                "user.roles",
                fn($q) => $q->whereIn("name", ["anggota", "danru"]),
            );
            $leaveQuery->whereHas(
                "user.roles",
                fn($q) => $q->whereIn("name", ["anggota", "danru"]),
            );
        }
    }

    private function applySearchFilter($attendanceQuery, $leaveQuery, $search)
    {
        if ($search) {
            $attendanceQuery->whereHas(
                "user",
                fn($q) => $q->where("name", "like", "%" . $search . "%"),
            );
            $leaveQuery->whereHas(
                "user",
                fn($q) => $q->where("name", "like", "%" . $search . "%"),
            );
        }
    }

    private function attachLeaveRequestsToAttendances($attendances, $leaveRequests)
    {
        foreach ($attendances as $attendance) {
            $leave = $leaveRequests->first(function ($item) use ($attendance) {
                return $item->user_id == $attendance->user_id;
            });
            if ($leave) {
                $attendance->leaveRequest = $leave;
            }
        }
    }

    private function getVirtualLeaveRecords($leaveRequests, $filterDate)
    {
        return $leaveRequests->map(function ($leave) use ($filterDate) {
            return (object) [
                "user" => $leave->user,
                "status" => "Izin",
                "type" => $leave->leave_type,
                "time_in" => $filterDate->copy()->startOfDay(), // for sorting
                "time_out" => null,
                "photo_in_path" => null,
                "latitude_in" => null,
                "longitude_in" => null,
                "photo_out_path" => null,
                "latitude_out" => null,
                "longitude_out" => null,
                "leaveRequest" => $leave,
            ];
        });
    }

    private function mergeRecords($attendances, $usersOnLeave)
    {
        $usersWithAttendance = $attendances->pluck("user_id");

        $filteredUsersOnLeave = $usersOnLeave->whereNotIn(
            "user.id",
            $usersWithAttendance,
        );

        return $attendances->toBase()->merge($filteredUsersOnLeave);
    }

    private function sortRecords($combined, $sortBy, $sortDirection)
    {
        if ($sortDirection === "desc") {
            return $combined->sortByDesc($sortBy)->values();
        }
        return $combined->sortBy($sortBy)->values();
    }

    private function paginateRecords($sorted, $perPage, $request)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $sorted->slice(
            ($currentPage - 1) * $perPage,
            $perPage,
        );

        return new LengthAwarePaginator(
            $currentPageItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            [
                "path" => LengthAwarePaginator::resolveCurrentPath(),
                "query" => $request->query(),
            ],
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $todayAttendance = Attendance::where("user_id", $user->id)
            ->whereDate("time_in", today())
            ->first();

        return view("attendances.create", compact("todayAttendance"));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "photo" => "required|image",
            "latitude" => "required|numeric",
            "longitude" => "required|numeric",
        ]);

        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            $now = now();

            // 1. Check for duplicate actions
            if ($error = $this->checkDuplicateAttendance($user, $now)) {
                return redirect()->back()->with("error", $error);
            }

            // 2. Validate Location
            $this->validateLocation($request);

            // 3. Determine Action (In/Out)
            $openAttendance = $this->findOpenAttendance($user, $now);

            // 4. Process Action
            try {
                $photoPath = $this->compressAndStoreImage($request->file('photo'));

                if ($openAttendance) {
                    // Clock Out
                    $this->handleClockOut($openAttendance, $request, $photoPath, $now);
                    $action = "out";
                } else {
                    // Clock In
                    if ($error = $this->checkClockInConstraints($user, $now)) {
                        // If constraint check fails, we must rollback file upload if possible, 
                        // but since we are in transaction, just throwing exception or returning error is enough.
                        // However, file is already stored. In a perfect world we'd delete it.
                        // For now, let's just return the error.
                        return redirect()->back()->with("error", $error);
                    }

                    $this->handleClockIn($user, $request, $photoPath, $now);
                    $action = "in";
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                // Log error and rethrow or handle
                throw $e;
            }

            $successMessage = "Absensi " . ($action === "in" ? "masuk" : "pulang") . " berhasil dicatat.";

            if ($request->expectsJson()) {
                return response()->json([
                    "message" => $successMessage,
                    "redirect_url" => route("attendances.index"),
                ]);
            }

            return redirect()->route("dashboard")->with("success", $successMessage);
        });
    }

    private function checkDuplicateAttendance($user, $now)
    {
        $twoHoursAgo = $now->copy()->subHours(2);
        $lastAction = Attendance::where("user_id", $user->id)
            ->where(function ($query) use ($twoHoursAgo) {
                $query
                    ->where("time_in", ">=", $twoHoursAgo)
                    ->orWhere("time_out", ">=", $twoHoursAgo);
            })
            ->latest("updated_at")
            ->first();

        if ($lastAction) {
            $lastActionTime = $lastAction->time_out ?? $lastAction->time_in;
            if ($lastAction->time_out && $lastAction->time_in) {
                $lastActionTime = $lastAction->time_out > $lastAction->time_in
                    ? $lastAction->time_out
                    : $lastAction->time_in;
            }
            return "Anda sudah melakukan absensi pada pukul " .
                Carbon::parse($lastActionTime)->format("H:i") .
                ". Aksi dibatalkan untuk mencegah data ganda.";
        }
        return null;
    }

    private function findOpenAttendance($user, $now)
    {
        $lookbehindTime = $now->copy()->subHours(16);
        $openAttendance = Attendance::where("user_id", $user->id)
            ->whereNull("time_out")
            ->where("time_in", ">=", $lookbehindTime)
            ->latest("time_in")
            ->first();

        if ($openAttendance && $openAttendance->time_in->diffInHours($now) > 24) {
            return null;
        }
        return $openAttendance;
    }

    private function checkClockInConstraints($user, $now)
    {
        // A) User already completed a shift today
        $completedToday = Attendance::where("user_id", $user->id)
            ->whereDate("time_in", $now->toDateString())
            ->whereNotNull("time_out")
            ->exists();

        if ($completedToday) {
            return "Anda sudah melakukan absensi datang dan pulang hari ini.";
        }

        // B) User tries to clock in too soon after a previous clock-out
        $lastCompletedAttendance = Attendance::where("user_id", $user->id)
            ->whereNotNull("time_out")
            ->latest("time_out")
            ->first();

        if ($lastCompletedAttendance && $lastCompletedAttendance->time_out->diffInMinutes($now) < 1) {
            return "Anda baru saja menyelesaikan absensi. Tidak dapat melakukan absensi masuk lagi dalam waktu singkat.";
        }

        // C) Check if user is on leave
        $dateString = $now->toDateString();
        $activeLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'disetujui')
            ->where('start_date', '<=', $dateString)
            ->where('end_date', '>=', $dateString)
            ->get()
            ->first(function ($leave) {
                return strtolower($leave->leave_type) !== 'izin terlambat';
            });

        if ($activeLeave) {
            return "Anda sedang dalam masa izin (" . $activeLeave->leave_type . "). Tidak dapat melakukan absensi.";
        }

        // D) Check clock-in window
        $pagiShiftStart = Carbon::parse($dateString . " 07:00");
        $malamShiftStart = Carbon::parse($dateString . " 19:00");

        $expectedStartTime = $now->hour >= 0 && $now->hour < 14
            ? $pagiShiftStart
            : $malamShiftStart;

        $windowStart = $expectedStartTime->copy()->subHour();
        $windowEnd = $expectedStartTime->copy()->addHour();

        if (!$now->between($windowStart, $windowEnd)) {
            return "Anda hanya bisa absen antara pukul " . $windowStart->format("H:i") . " dan " . $windowEnd->format("H:i") . ".";
        }

        return null;
    }

    private function validateLocation(Request $request)
    {
        $settingKeys = ["center_latitude", "center_longitude", "allowed_radius_meters"];
        $settings = Setting::whereIn("key", $settingKeys)->pluck("value", "key");

        if (count($settingKeys) === $settings->count()) {
            $distance = $this->calculateDistance(
                $settings["center_latitude"],
                $settings["center_longitude"],
                $request->latitude,
                $request->longitude
            );

            if ($distance > $settings["allowed_radius_meters"]) {
                throw ValidationException::withMessages([
                    "location" => "Anda berada di luar radius lokasi yang diizinkan untuk absensi. Jarak Anda: " . round($distance) . " meter dari pusat.",
                ]);
            }
        }
    }

    private function handleClockIn($user, $request, $photoPath, $now)
    {
        $dateString = $now->toDateString();
        $pagiShiftStart = Carbon::parse($dateString . " 07:00");
        $malamShiftStart = Carbon::parse($dateString . " 19:00");

        $expectedStartTime = $now->hour >= 0 && $now->hour < 14
            ? $pagiShiftStart
            : $malamShiftStart;

        $status = $now->isAfter($expectedStartTime) ? "Terlambat" : "Tepat Waktu";

        Attendance::create([
            "user_id" => $user->id,
            "time_in" => $now,
            "photo_in_path" => $photoPath,
            "latitude_in" => $request->latitude,
            "longitude_in" => $request->longitude,
            "status" => $status,
        ]);
    }

    private function handleClockOut($attendance, $request, $photoPath, $now)
    {
        $timeIn = $attendance->time_in;
        $timeOut = $now;

        // Calculate the midpoint of the user's actual shift duration
        $actualShiftMidpoint = $timeIn->copy()->addSeconds($timeIn->diffInSeconds($timeOut) / 2);

        // Define the ideal shifts relative to the clock-in day
        $timeInDateString = $timeIn->toDateString();
        $shifts = [
            "Reguler" => [
                "start" => Carbon::parse("$timeInDateString 07:00"),
                "end" => Carbon::parse("$timeInDateString 15:00"), // 8 hours
            ],
            "Normal Pagi" => [
                "start" => Carbon::parse("$timeInDateString 07:00"),
                "end" => Carbon::parse("$timeInDateString 19:00"), // 12 hours
            ],
            "Normal Malam" => [
                "start" => Carbon::parse("$timeInDateString 19:00"),
                "end" => Carbon::parse("$timeInDateString 07:00")->addDay(), // 12 hours
            ],
        ];

        $closestShiftName = null;
        $minimumDistance = PHP_INT_MAX;

        // Find the ideal shift with the closest midpoint to the actual shift's midpoint
        foreach ($shifts as $shiftName => $shiftTimes) {
            $idealStart = $shiftTimes["start"];
            $idealEnd = $shiftTimes["end"];
            $idealMidpoint = $idealStart->copy()->addSeconds($idealStart->diffInSeconds($idealEnd) / 2);

            $distance = abs($actualShiftMidpoint->getTimestamp() - $idealMidpoint->getTimestamp());

            if ($distance < $minimumDistance) {
                $minimumDistance = $distance;
                $closestShiftName = $shiftName;
            }
        }

        // Override logic for Reguler vs Normal Pagi
        $actualDurationHours = $timeIn->diffInHours($timeOut);
        if ($closestShiftName === "Normal Pagi" && $actualDurationHours < 10) {
            $type = "Reguler";
        } else {
            $type = $closestShiftName;
        }

        $attendance->update([
            "time_out" => $now,
            "photo_out_path" => $photoPath,
            "latitude_out" => $request->latitude,
            "longitude_out" => $request->longitude,
            "type" => $type,
        ]);
    }

    /**
     * Calculate the distance between two points on Earth.
     * @return float Distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }



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
                $path = $file->storeAs('satpam/attendances/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
                if ($path === false) {
                    throw ValidationException::withMessages([
                        'photo' => 'Gagal mengunggah foto (format tidak didukung). Silakan coba lagi.',
                    ]);
                }
                return $path;
        }

        if (!$imageResource) {
            // Fallback if image resource creation failed
            $path = $file->storeAs('satpam/attendances/' . Auth::id(), $accountName . '-' . $timestamp . '.' . $originalExtension, 'public');
            if ($path === false) {
                throw ValidationException::withMessages([
                    'photo' => 'Gagal mengunggah foto (fallback). Silakan coba lagi.',
                ]);
            }
            return $path;
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

        // Handle EXIF Rotation
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
                            // Swap dimensions for 90 degree rotation
                            $tempWidth = $newWidth;
                            $newWidth = $newHeight;
                            $newHeight = $tempWidth;
                            break;
                        case 8:
                            $imageResource = imagerotate($imageResource, 90, 0);
                            // Swap dimensions for 90 degree rotation
                            $tempWidth = $newWidth;
                            $newWidth = $newHeight;
                            $newHeight = $tempWidth;
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Ignore EXIF errors
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
        $storagePath = 'satpam/attendances/' . $year . '/' . $month . '/' . $filename;
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
        $directoryPath = 'satpam/attendances/' . $year . '/' . $month;
        if (!Storage::disk('public')->exists($directoryPath)) {
            Storage::disk('public')->makeDirectory($directoryPath);
        }

        // Upload to Public Disk
        $fileHandle = fopen($tempPath, 'r');
        $uploadResult = Storage::disk('public')->put($storagePath, $fileHandle);
        fclose($fileHandle);

        if (!$uploadResult) {
            unlink($tempPath);
            throw ValidationException::withMessages([
                'photo' => 'Gagal mengunggah foto ke penyimpanan.',
            ]);
        }

        // Clean up temp file
        unlink($tempPath);

        // Free up memory
        imagedestroy($imageResource);
        imagedestroy($newImageResource);

        return $storagePath;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showExportForm()
    {
        $this->authorize("export", Attendance::class);
        return view("attendances.export-form");
    }

    public function exportPdf(Request $request)
    {
        $this->authorize("export", Attendance::class);

        $request->validate([
            "month" => "required|date_format:Y-m",
        ]);

        $carbonDate = Carbon::createFromFormat("Y-m", $request->month);
        $startDate = $carbonDate->copy()->startOfMonth();
        $endDate = $carbonDate->copy()->endOfMonth();
        $monthName = $carbonDate->translatedFormat("F Y");

        $users = $this->getUsersForExport($startDate);
        $attendances = $this->getAttendancesForExport($startDate, $endDate);
        $leaveRequests = $this->getLeaveRequestsForExport($startDate, $endDate);
        $dateRange = CarbonPeriod::create($startDate, $endDate);

        $dataMatrix = $this->buildExportDataMatrix(
            $users,
            $attendances,
            $leaveRequests,
            $dateRange
        );

        $pdf = Pdf::loadView("attendances.export-pdf", [
            "dataMatrix" => $dataMatrix,
            "dateRange" => $dateRange,
            "monthName" => $monthName,
        ])->setPaper("a3", "landscape");

        $filename = "Laporan_Absensi_" . Str::slug($monthName) . ".pdf";
        return $pdf->stream($filename);
    }

    private function getUsersForExport($startDate)
    {
        return User::withTrashed()
            ->whereHas("roles", function ($query) {
                $query->whereIn("name", ["danru", "anggota", "backup"]);
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('deleted_at')
                    ->orWhere('deleted_at', '>=', $startDate);
            })
            ->orderBy("name")
            ->get();
    }

    private function getAttendancesForExport($startDate, $endDate)
    {
        return Attendance::with("user")
            ->whereBetween("time_in", [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return $item->user_id .
                    "_" .
                    Carbon::parse($item->time_in)->format("Y-m-d");
            });
    }

    private function getLeaveRequestsForExport($startDate, $endDate)
    {
        return LeaveRequest::with("user")
            ->where("status", "disetujui")
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->where("start_date", "<=", $endDate->format("Y-m-d"))
                    ->where("end_date", ">=", $startDate->format("Y-m-d"));
            })
            ->get();
    }

    private function buildExportDataMatrix($users, $attendances, $leaveRequests, $dateRange)
    {
        $dataMatrix = [];

        foreach ($users as $user) {
            $dataMatrix[$user->id]["user_name"] = $user->name;
            $dataMatrix[$user->id]["dates"] = [];

            foreach ($dateRange as $date) {
                $dateString = $date->format("Y-m-d");
                $dataMatrix[$user->id]["dates"][$dateString] = $this->calculateDailyStatus(
                    $user,
                    $date,
                    $attendances,
                    $leaveRequests
                );
            }
        }

        return $dataMatrix;
    }

    private function calculateDailyStatus($user, $date, $attendances, $leaveRequests)
    {
        $dateString = $date->format("Y-m-d");
        $attendanceKey = $user->id . "_" . $dateString;

        if (isset($attendances[$attendanceKey])) {
            $attendance = $attendances[$attendanceKey];
            $timeIn = Carbon::parse($attendance->time_in);

            // Lateness logic
            $pagiShiftStart = $timeIn->copy()->setTime(7, 0, 0);
            $malamShiftStart = $timeIn->copy()->setTime(19, 0, 0);

            $expectedStartTime =
                $timeIn->hour >= 0 && $timeIn->hour < 14
                ? $pagiShiftStart
                : $malamShiftStart;
            $isLate = $timeIn->isAfter($expectedStartTime);

            return [
                "type" => $attendance->type ?? "N/A",
                "time_in" => $timeIn->format("H:i:s"),
                "time_out" => $attendance->time_out
                    ? Carbon::parse($attendance->time_out)->format("H:i:s")
                    : "-",
                "is_late" => $isLate,
                "status" => "Hadir",
            ];
        }

        // Check if the user is on leave
        foreach ($leaveRequests as $leave) {
            if (
                $leave->user_id == $user->id &&
                $date->between($leave->start_date, $leave->end_date)
            ) {
                return [
                    "status" => "Izin",
                    "type" => $leave->leave_type,
                ];
            }
        }

        return [
            "status" => "Tidak Hadir",
        ];
    }
}
