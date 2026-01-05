<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteExpiredMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired media files from reports and attendance based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting media deletion process...');

        $this->deleteExpiredReportMedia();
        $this->deleteExpiredAttendanceMedia();

        $this->info('Media deletion process completed.');
    }

    private function deleteExpiredReportMedia()
    {
        $reportTypes = \App\Models\ReportType::where(function ($query) {
            $query->whereNotNull('retention_days_images')
                ->orWhereNotNull('retention_days_videos');
        })->get();

        foreach ($reportTypes as $reportType) {
            // Process Images
            if ($reportType->retention_days_images) {
                $this->processReportMedia($reportType, 'image', $reportType->retention_days_images);
            }

            // Process Videos
            if ($reportType->retention_days_videos) {
                $this->processReportMedia($reportType, 'video', $reportType->retention_days_videos);
            }
        }
    }

    private function processReportMedia($reportType, $mediaType, $retentionDays)
    {
        $retentionDate = now()->subDays($retentionDays);
        $reports = \App\Models\Report::where('report_type_id', $reportType->id)
            ->where('created_at', '<', $retentionDate)
            ->get();

        $this->info("Checking Report Type: {$reportType->name} for {$mediaType}s (Retention: {$retentionDays} days) - Found {$reports->count()} expired reports.");

        foreach ($reports as $report) {
            $schema = $report->reportType->reportTypeFields ?? [];
            $data = $report->data;
            $updated = false;

            foreach ($schema as $field) {
                // Check field type matches media type
                $isTargetField = ($mediaType === 'image' && $field->type === 'file') ||
                    ($mediaType === 'video' && $field->type === 'video');

                if ($isTargetField && !empty($data[$field->name])) {
                    $files = $data[$field->name];
                    $fieldUpdated = false;

                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                                $this->info("Deleted {$mediaType} from Public: {$file}");
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
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($files)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($files);
                            $this->info("Deleted {$mediaType} from Public: {$files}");
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
            }
        }
    }

    private function deleteExpiredAttendanceMedia()
    {
        $retentionDays = \App\Models\Setting::where('key', 'attendance_retention_days')->value('value');

        if ($retentionDays) {
            $retentionDate = now()->subDays($retentionDays);
            $attendances = \App\Models\Attendance::where('created_at', '<', $retentionDate)
                ->where(function ($query) {
                    $query->whereNotNull('photo_in_path')
                        ->orWhereNotNull('photo_out_path');
                })
                ->get();

            $this->info("Checking Attendance (Retention: {$retentionDays} days) - Found {$attendances->count()} expired records.");

            foreach ($attendances as $attendance) {
                // Process Photo In
                if ($attendance->photo_in_path) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->photo_in_path);
                        $this->info("Deleted attendance photo in from Public: {$attendance->photo_in_path}");
                        $attendance->photo_in_path = null;
                    }
                }

                // Process Photo Out
                if ($attendance->photo_out_path) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->photo_out_path);
                        $this->info("Deleted attendance photo out from Public: {$attendance->photo_out_path}");
                        $attendance->photo_out_path = null;
                    }
                }

                $attendance->save();
            }
        } else {
            $this->info('No retention policy set for Attendance.');
        }
    }
}
