<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny,App\Models\Attendance')->only('index');
        $this->middleware('can:create,App\Models\Attendance')->only(['create', 'store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Attendance::with('user.roles')->latest('time_in');

        // Roles that can see all attendances: superadmin, manajemen, danru
        if ($user->hasRole(['superadmin', 'manajemen', 'danru'])) {
            $attendances = $query->paginate(15);
        } else {
            // All other roles (e.g., 'anggota') can only see their own attendances
            $attendances = $query->where('user_id', $user->id)->paginate(15);
        }

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('time_in', today())
            ->first();

        return view('attendances.create', compact('todayAttendance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $now = now();

        // Attempt to find an open attendance record for the user
        $openAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('time_out')
            ->latest('time_in')
            ->first();

        // If an open attendance exists, but it's older than 24 hours,
        // we consider it stale and proceed as if no open attendance was found.
        if ($openAttendance && $openAttendance->time_in->diffInHours($now) > 24) {
            $openAttendance = null;
        }

        // Determine the intended action (clock-in or clock-out)
        $action = 'in';
        $attendanceToUpdate = null; // Initialize attendanceToUpdate

        if ($openAttendance) {
            // Case 1: An active, open attendance record exists. This must be a clock-out.
            $action = 'out';
            $attendanceToUpdate = $openAttendance; // This is the record to update
        } else {
            // Case 2: No active, open attendance record found. This must be a clock-in.

            // Before allowing a clock-in, check for conditions that prevent it:
            // A) User already completed a shift today (for non-night shifts)
            $completedToday = Attendance::where('user_id', $user->id)
                ->whereDate('time_in', $now->toDateString())
                ->whereNotNull('time_out')
                ->exists();

            if ($completedToday) {
                return redirect()->back()->with('error', 'Anda sudah melakukan absensi datang dan pulang hari ini.');
            }

            // B) User tries to clock in too soon after a previous clock-out (e.g., trying to clock out twice)
            // This handles the "double clock-out" scenario where the system would otherwise try to clock them in again.
            $lastCompletedAttendance = Attendance::where('user_id', $user->id)
                ->whereNotNull('time_out')
                ->latest('time_out') // Look at the latest clock-out time
                ->first();

            // If the last clock-out was very recent (e.g., within 1 minute), prevent a new clock-in.
            // This catches accidental double-taps or attempts to clock out when already clocked out.
            if ($lastCompletedAttendance && $lastCompletedAttendance->time_out->diffInMinutes($now) < 1) {
                return redirect()->back()->with('error', 'Anda baru saja menyelesaikan absensi. Tidak dapat melakukan absensi masuk lagi dalam waktu singkat.');
            }
        }

        // Location validation
        $settingKeys = ['center_latitude', 'center_longitude', 'allowed_radius_meters'];
        $settings = Setting::whereIn('key', $settingKeys)->pluck('value', 'key');

        if (count($settingKeys) === $settings->count()) {
            $centerLat = $settings['center_latitude'];
            $centerLon = $settings['center_longitude'];
            $allowedRadius = $settings['allowed_radius_meters'];
            $userLat = $request->latitude;
            $userLon = $request->longitude;

            $distance = $this->calculateDistance($centerLat, $centerLon, $userLat, $userLon);

            if ($distance > $allowedRadius) {
                throw ValidationException::withMessages([
                    'location' => 'Anda berada di luar radius lokasi yang diizinkan untuk absensi. Jarak Anda: ' . round($distance) . ' meter dari pusat.',
                ]);
            }
        }

        $file = $request->file('photo');
        $photoPath = null;

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
            // Rotate image if it's landscape to make it portrait
            $imageResource = $this->rotateLandscapeToPortrait($imageResource);

            $year = now()->format('Y');
            $month = now()->format('m');
            $storagePath = 'attendances/' . $year . '/' . $month . '/' . $filename;
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

            $photoPath = $storagePath;
        } else {
            // Fallback for unsupported image types (e.g., webp, heic)
            $year = now()->format('Y');
            $month = now()->format('m');
            $photoPath = $file->store('attendances/' . $year . '/' . $month, 'public');
        }
        // --- End of Native GD Logic ---

        if ($action === 'in') {
            // Time restriction logic (temporarily disabled for testing)
            $status = 'Tepat Waktu'; // Default status when restrictions are off
            $now = now();
            // $dateString = $now->toDateString();

            // // Determine expected shift based on current time
            // $pagiShiftStart = \Carbon\Carbon::parse($dateString . ' 07:00');
            // $malamShiftStart = \Carbon\Carbon::parse($dateString . ' 19:00');

            // $expectedStartTime = null;
            // // If current time is before 2 PM, assume it's for the morning shift. Otherwise, night shift.
            // if ($now->hour < 14) {
            //     $expectedStartTime = $pagiShiftStart;
            // } else {
            //     $expectedStartTime = $malamShiftStart;
            // }

            // $windowStart = $expectedStartTime->copy()->subHour();
            // $windowEnd = $expectedStartTime->copy()->addHour();

            // // Check if user is clocking in too early
            // if ($now->isBefore($windowStart)) {
            //     $errorMessage = 'Anda tidak dapat absen terlalu pagi. Anda dapat absen mulai pukul ' . $windowStart->format('H:i') . '.';
            //     if ($request->expectsJson()) {
            //         return response()->json(['message' => $errorMessage], 422);
            //     }
            //     return redirect()->back()->with('error', $errorMessage);
            // }

            // // Check if user is clocking in within the allowed window but late
            // if ($now->isAfter($expectedStartTime) && $now->isBefore($windowEnd)) {
            //     $status = 'Terlambat';
            // } elseif ($now->isAfter($windowEnd)) {
            //     // Also consider clocking in after the 1-hour window as late, as per stakeholder request
            //     $status = 'Terlambat';
            // }


            Attendance::create([
                'user_id' => $user->id,
                'time_in' => $now,
                'photo_in_path' => $photoPath,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'status' => $status,
            ]);
        } else {
            // Determine attendance type by finding the closest shift schedule
            $timeIn = \Carbon\Carbon::parse($attendance->time_in);
            $timeOut = $now;

            // Calculate the midpoint of the user's actual shift duration
            $actualShiftMidpoint = $timeIn->copy()->addSeconds($timeIn->diffInSeconds($timeOut) / 2);

            // Define the ideal shifts relative to the clock-in day
            $timeInDateString = $timeIn->toDateString();
            $shifts = [
                'Reguler' => [
                    'start' => \Carbon\Carbon::parse($timeInDateString . ' 07:00'),
                    'end' => \Carbon\Carbon::parse($timeInDateString . ' 15:00'), // 8 hours
                ],
                'Normal Pagi' => [
                    'start' => \Carbon\Carbon::parse($timeInDateString . ' 07:00'),
                    'end' => \Carbon\Carbon::parse($timeInDateString . ' 19:00'), // 12 hours
                ],
                'Normal Malam' => [
                    'start' => \Carbon\Carbon::parse($timeInDateString . ' 19:00'),
                    'end' => \Carbon\Carbon::parse($timeInDateString . ' 07:00')->addDay(), // 12 hours
                ],
            ];

            $closestShiftName = null;
            $minimumDistance = PHP_INT_MAX;

            // Find the ideal shift with the closest midpoint to the actual shift's midpoint
            foreach ($shifts as $shiftName => $shiftTimes) {
                $idealStart = $shiftTimes['start'];
                $idealEnd = $shiftTimes['end'];
                $idealMidpoint = $idealStart->copy()->addSeconds($idealStart->diffInSeconds($idealEnd) / 2);

                $distance = abs($actualShiftMidpoint->getTimestamp() - $idealMidpoint->getTimestamp());

                if ($distance < $minimumDistance) {
                    $minimumDistance = $distance;
                    $closestShiftName = $shiftName;
                }
            }

            // This logic helps distinguish between Reguler and Normal Pagi, which have the same start time.
            // If the closest shift is Normal Pagi, but the actual duration is closer to a Reguler shift (8h)
            // than a Normal Pagi shift (12h), we override it to Reguler.
            $actualDurationHours = $timeIn->diffInHours($timeOut);
            if ($closestShiftName === 'Normal Pagi' && $actualDurationHours < 10) { // 10 hours is a threshold between 8 and 12
                $type = 'Reguler';
            } else {
                $type = $closestShiftName;
            }

            $attendanceToUpdate->update([
                'time_out' => $now,
                'photo_out_path' => $photoPath,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
                'type' => $type,
            ]);
        }

        $successMessage = 'Absensi ' . ($action === 'in' ? 'masuk' : 'pulang') . ' berhasil dicatat.';
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $successMessage,
                'redirect_url' => route('attendances.index')
            ]);
        }

        return redirect()->route('dashboard')->with('success', $successMessage);
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

        $a = sin($dLat / 2) * sin($dLat / 2) +
           cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
           sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Rotates a GD image resource if it's in landscape orientation to make it portrait.
     *
     * @param resource $imageResource The GD image resource.
     * @return resource The rotated image resource.
     */
    private function rotateLandscapeToPortrait($imageResource)
    {
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);

        if ($width > $height) {
            // Image is landscape, rotate 90 degrees clockwise to make it portrait
            $imageResource = imagerotate($imageResource, 270, 0); // 270 degrees is 90 degrees clockwise
        }

        return $imageResource;
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
}

