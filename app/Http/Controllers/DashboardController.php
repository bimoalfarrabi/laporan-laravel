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

        $approvedReports = Report::with('user.roles', 'reportType')
            ->where('status', 'disetujui')
            ->latest()
            ->paginate(5, ['*'], 'approved_reports_page');

        $reportsForApprovalQuery = Report::with('user.roles', 'reportType')
            ->where('status', 'belum disetujui')
            ->latest();

        if ($user->hasRole('danru')) {
            $reportsForApprovalQuery->whereHas('user', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['anggota', 'backup']);
                });
            });
        } elseif ($user->hasRole('manajemen')) {
            $reportsForApprovalQuery->whereHas('user', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'danru');
                });
            });
        }

        $reportsForApproval = $reportsForApprovalQuery->paginate(5, ['*'], 'reports_for_approval_page');

        if ($request->ajax()) {
            if ($request->has('approved_reports_page')) {
                return view('partials.approved-reports', compact('approvedReports'));
            }
            if ($request->has('reports_for_approval_page')) {
                return view('partials.reports-for-approval', compact('reportsForApproval'));
            }
        }

        $viewData['announcements'] = Announcement::with('user.roles')
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
            $viewData['reportsForApproval'] = $reportsForApproval;
            $viewData['approvedReports'] = $approvedReports;
            $viewData['pendingLeaveRequests'] = \App\Models\LeaveRequest::with('user.roles')
                ->where('status', 'menunggu persetujuan')
                ->latest()
                ->get();
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user.roles')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->hasRole('manajemen')) {
            $viewData['reportsForApproval'] = $reportsForApproval;
            $viewData['approvedReports'] = $approvedReports;
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user.roles')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->hasRole(['anggota', 'backup'])) {
            $viewData['myRecentReports'] = Report::with('user.roles', 'reportType')->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
            $viewData['approvedReports'] = $approvedReports;
            $viewData['myLeaveRequests'] = \App\Models\LeaveRequest::with('user.roles')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        } elseif ($user->hasRole('superadmin')) {
            $viewData['totalUsers'] = User::count();
            $viewData['reportStats'] = Report::query()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $viewData['recentReports'] = Report::with('user.roles', 'reportType')->latest()->take(5)->get();
            $viewData['latestLeaveRequests'] = \App\Models\LeaveRequest::with('user.roles')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', $viewData);
    }
}
