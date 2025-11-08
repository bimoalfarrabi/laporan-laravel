<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $path = $request->file('photo')->store('attendances', 'public');

        Attendance::create([
            'user_id' => Auth::id(),
            'photo_path' => $path,
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
