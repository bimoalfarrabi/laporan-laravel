<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $query = Attendance::with('user')->latest();

        if ($user->hasRole(['superadmin', 'manajemen'])) {
            // Superadmin & Manajemen can see all attendances
            $attendances = $query->paginate(15);
        } elseif ($user->hasRole('danru')) {
            // Danru can see their own and all anggota's attendances
            // Note: This assumes no direct team structure. A more complex system
            // might require a 'team_id' or 'supervisor_id' on the users table.
            $attendances = $query->whereHas('user.roles', function ($q) {
                $q->whereIn('name', ['anggota', 'danru']);
            })->paginate(15);
        } else {
            // Anggota can only see their own attendances
            $attendances = $query->where('user_id', $user->id)->paginate(15);
        }

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('attendances.create');
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

        Attendance::create([
            'user_id' => Auth::id(),
            'photo_path' => $photoPath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('dashboard')->with('success', 'Absensi berhasil dicatat.');
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
