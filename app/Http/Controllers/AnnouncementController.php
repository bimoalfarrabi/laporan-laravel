<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:danru|superadmin')->except(['show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Announcement::with('user')
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($sortBy == 'user_name') {
            $query->join('users', 'announcements.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('announcements.*'); // Hindari ambiguitas kolom
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $announcements = $query->get();

        return view('announcements.index', compact('announcements', 'sortBy', 'sortDirection'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        Announcement::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $request->input('expires_at'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $announcement->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $request->input('expires_at'),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete(); // Soft delete

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function archive()
    {
        $announcements = Announcement::onlyTrashed()->with('user')->latest()->get();
        return view('announcements.archive', compact('announcements'));
    }

    public function restore($id)
    {
        $announcement = Announcement::withTrashed()->findOrFail($id);
        $announcement->restore();

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $this->authorize('announcements:force-delete');
        $announcement = Announcement::withTrashed()->findOrFail($id);
        $announcement->forceDelete();

        return redirect()->route('announcements.archive')->with('success', 'Pengumuman berhasil dihapus permanen.');
    }
}
