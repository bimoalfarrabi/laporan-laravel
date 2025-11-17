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
        $query = Attendance::with('user')->latest('time_in');

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

        // Find today's attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('time_in', $now->toDateString())
            ->first();

        $action = 'in';
        if ($attendance && $attendance->time_out) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absensi datang dan pulang hari ini.');
        } elseif ($attendance) {
            $action = 'out';
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
            // Time restriction logic
            $status = 'Tepat Waktu';
            $now = now();
            $dateString = $now->toDateString();

            // Determine expected shift based on current time
            $pagiShiftStart = \Carbon\Carbon::parse($dateString . ' 07:00');
            $malamShiftStart = \Carbon\Carbon::parse($dateString . ' 19:00');

            $expectedStartTime = null;
            // If current time is before 2 PM, assume it's for the morning shift. Otherwise, night shift.
            if ($now->hour < 14) {
                $expectedStartTime = $pagiShiftStart;
            } else {
                $expectedStartTime = $malamShiftStart;
            }

            $windowStart = $expectedStartTime->copy()->subHour();
            $windowEnd = $expectedStartTime->copy()->addHour();

            // Check if user is clocking in too early
            if ($now->isBefore($windowStart)) {
                $errorMessage = 'Anda tidak dapat absen terlalu pagi. Anda dapat absen mulai pukul ' . $windowStart->format('H:i') . '.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $errorMessage], 422);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            // Check if user is clocking in within the allowed window but late
            if ($now->isAfter($expectedStartTime) && $now->isBefore($windowEnd)) {
                $status = 'Terlambat';
            } elseif ($now->isAfter($windowEnd)) {
                // Also consider clocking in after the 1-hour window as late, as per stakeholder request
                $status = 'Terlambat';
            }


            Attendance::create([
                'user_id' => $user->id,
                'time_in' => $now,
                'photo_in_path' => $photoPath,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'status' => $status,
            ]);
        } else {
            // Determine attendance type
            $timeIn = \Carbon\Carbon::parse($attendance->time_in);
            $timeOut = $now;
            $type = null;

            $dateString = $timeIn->toDateString();

            // Define time windows
            $regulerStart = \Carbon\Carbon::parse($dateString . ' 07:00');
            $regulerEnd = \Carbon\Carbon::parse($dateString . ' 15:00');

            $normalPagiStart = \Carbon\Carbon::parse($dateString . ' 07:00');
            $normalPagiEnd = \Carbon\Carbon::parse($dateString . ' 19:00');

            $normalMalamStart = \Carbon\Carbon::parse($dateString . ' 19:00');
            $normalMalamEnd = \Carbon\Carbon::parse($dateString . ' 07:00')->addDay();

            if ($timeIn >= $regulerStart && $timeOut <= $regulerEnd) {
                $type = 'Reguler';
            } elseif ($timeIn >= $normalPagiStart && $timeOut <= $normalPagiEnd) {
                $type = 'Normal Pagi';
            } elseif ($timeIn >= $normalMalamStart && $timeOut <= $normalMalamEnd) {
                $type = 'Normal Malam';
            }

            $attendance->update([
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

