<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Report;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MediaManagementController extends Controller
{
    public function index()
    {
        $reportTypes = ReportType::all();
        return view('media.index', compact('reportTypes'));
    }

    public function deleteReports(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type_id' => 'nullable|exists:report_types,id',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $reportTypeId = $request->report_type_id;

        $query = Report::whereBetween('created_at', [$startDate, $endDate]);

        if ($reportTypeId) {
            $query->where('report_type_id', $reportTypeId);
        }

        $reports = $query->get();
        $count = 0;

        foreach ($reports as $report) {
            $schema = $report->reportType->reportTypeFields ?? [];
            $data = $report->data;
            $updated = false;

            foreach ($schema as $field) {
                if (($field->type === 'file' || $field->type === 'video') && !empty($data[$field->name])) {
                    $files = $data[$field->name];
                    $fieldUpdated = false;

                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if (Storage::disk('public')->exists($file)) {
                                Storage::disk('public')->delete($file);
                                $fieldUpdated = true;
                            }
                        }
                        if ($fieldUpdated) {
                            $data[$field->name] = []; // Reset to empty array
                            // Set status flag
                            $data['_media_status'][$field->name] = 'deleted';
                            $data['_media_status'][$field->name . '_deleted_at'] = now()->toIso8601String();
                            $updated = true;
                        }
                    } else {
                        if (Storage::disk('public')->exists($files)) {
                            Storage::disk('public')->delete($files);
                            $data[$field->name] = null;
                            // Set status flag
                            $data['_media_status'][$field->name] = 'deleted';
                            $data['_media_status'][$field->name . '_deleted_at'] = now()->toIso8601String();
                            $updated = true;
                        }
                    }
                }
            }

            if ($updated) {
                $report->data = $data;
                $report->save();
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil menghapus media dari {$count} laporan.");
    }

    public function deleteAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $attendances = Attendance::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNotNull('photo_in_path')
                    ->orWhereNotNull('photo_out_path');
            })
            ->get();

        $count = 0;

        foreach ($attendances as $attendance) {
            $updated = false;

            if ($attendance->photo_in_path) {
                if (Storage::disk('public')->exists($attendance->photo_in_path)) {
                    Storage::disk('public')->delete($attendance->photo_in_path);
                    $attendance->photo_in_path = null;
                    $updated = true;
                }
            }

            if ($attendance->photo_out_path) {
                if (Storage::disk('public')->exists($attendance->photo_out_path)) {
                    Storage::disk('public')->delete($attendance->photo_out_path);
                    $attendance->photo_out_path = null;
                    $updated = true;
                }
            }

            if ($updated) {
                $attendance->save();
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil menghapus media dari {$count} data absensi.");
    }
}
