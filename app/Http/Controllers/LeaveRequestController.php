<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $user = Auth::user();
        $query = LeaveRequest::with(['user.roles', 'approvedBy', 'rejectedBy'])->latest();

        // Role-based filtering
        if ($user->hasRole('anggota')) {
            $query->where('user_id', $user->id);
        }

        // Search and filter
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->paginate(15)->appends($request->query());

        if ($request->ajax()) {
            return view('leave-requests._results', compact('leaveRequests'))->render();
        }

        return view('leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', LeaveRequest::class);
        return view('leave-requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);

        $request->validate([
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);
        $leaveRequest->load(['user.roles', 'approvedBy.roles', 'rejectedBy.roles']);
        return view('leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);
        // For now, we don't have an edit view.
        // If needed, create a view similar to 'create.blade.php'
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);
        // For now, we don't have an edit view.
        abort(404);
    }

    /**
     * Approve the specified leave request.
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorize('approveOrReject', $leaveRequest);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
        ]);

        return redirect()->route('leave-requests.show', $leaveRequest)->with('success', 'Pengajuan cuti telah disetujui.');
    }

    /**
     * Reject the specified leave request.
     */
    public function reject(LeaveRequest $leaveRequest)
    {
        $this->authorize('approveOrReject', $leaveRequest);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('leave-requests.show', $leaveRequest)->with('success', 'Pengajuan cuti telah ditolak.');
    }

    /**
     * Export the specified leave request to PDF.
     */
    public function exportPdf(LeaveRequest $leaveRequest)
    {
        $this->authorize('exportPdf', $leaveRequest);

        $leaveRequest->load(['user.roles', 'approvedBy.roles']);

        $pdf = Pdf::loadView('leave-requests.pdf', compact('leaveRequest'));
        
        $applicantName = Str::slug($leaveRequest->user->name);
        $startDate = $leaveRequest->start_date->format('Ymd');
        $filename = "surat-cuti-{$applicantName}-{$startDate}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->authorize('delete', $leaveRequest);
        $leaveRequest->delete();
        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan cuti berhasil dihapus.');
    }
}