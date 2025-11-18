<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\User;
use App\Models\Announcement;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $viewData = [];

        $viewData['announcements'] = Announcement::with('user')
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->take(5)
            ->get();

        if ($user->hasRole('danru')) {
            $viewData['reportsForApproval'] = Report::with('user', 'reportType')
                ->where('status', 'belum disetujui')
                ->whereHas('user', function ($query) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'anggota');
                    });
                })
                ->latest()
                ->get();
            $viewData['approvedReports'] = Report::with('user', 'reportType')
                ->where('status', 'disetujui')
                ->latest()
                ->paginate(5);
            $viewData['pendingLeaveRequests'] = \App\Models\LeaveRequest::with('user')
                ->where('status', 'menunggu persetujuan')
                ->latest()
                ->get();
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->hasRole('manajemen')) {
            $viewData['reportsForApproval'] = Report::with('user', 'reportType')
                ->where('status', 'belum disetujui')
                ->whereHas('user', function ($query) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'danru');
                    });
                })
                ->latest()
                ->get();
            $viewData['approvedReports'] = Report::with('user', 'reportType')
                ->where('status', 'disetujui')
                ->latest()
                ->paginate(5);
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->hasRole('anggota')) {
            $viewData['myRecentReports'] = Report::with('user', 'reportType')->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
            $viewData['approvedReports'] = Report::with('user', 'reportType')
                ->where('status', 'disetujui')
                ->latest()
                ->paginate(5);
            $viewData['myLeaveRequests'] = \App\Models\LeaveRequest::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        } elseif ($user->hasRole('superadmin')) {
            $viewData['totalUsers'] = User::count();
            $viewData['reportStats'] = Report::query()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $viewData['recentReports'] = Report::with('user', 'reportType')->latest()->take(5)->get();
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', $viewData);
    }
}
