<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_it_deletes_expired_report_images()
    {
        // 1. Setup Report Type with Image Retention
        $reportType = ReportType::create([
            'name' => 'Test Report',
            'slug' => 'test-report',
            'is_active' => true,
            'retention_days_images' => 30, // 30 days retention
            'retention_days_videos' => null,
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        $reportType->reportTypeFields()->create([
            'label' => 'Foto',
            'name' => 'foto',
            'type' => 'file',
            'order' => 1,
        ]);

        // 2. Create Old Report (Expired)
        $oldFile = 'reports/old_image.jpg';
        Storage::disk('public')->put($oldFile, 'content');

        $oldReport = Report::create([
            'report_type_id' => $reportType->id,
            'user_id' => User::factory()->create()->id,
            'data' => ['foto' => $oldFile],
        ]);
        $oldReport->created_at = now()->subDays(31);
        $oldReport->save();

        // 3. Create New Report (Not Expired)
        $newFile = 'reports/new_image.jpg';
        Storage::disk('public')->put($newFile, 'content');

        $newReport = Report::create([
            'report_type_id' => $reportType->id,
            'user_id' => User::factory()->create()->id,
            'data' => ['foto' => $newFile],
        ]);
        $newReport->created_at = now()->subDays(10);
        $newReport->save();

        // 4. Run Command
        $this->artisan('media:delete-expired')
            ->assertExitCode(0);

        // 5. Assertions
        Storage::disk('public')->assertMissing($oldFile);
        Storage::disk('public')->assertExists($newFile);

        $this->assertNull($oldReport->fresh()->data['foto']);
        $this->assertEquals($newFile, $newReport->fresh()->data['foto']);
    }

    public function test_it_deletes_expired_report_videos()
    {
        // 1. Setup Report Type with Video Retention
        $reportType = ReportType::create([
            'name' => 'Video Report',
            'slug' => 'video-report',
            'is_active' => true,
            'retention_days_images' => null,
            'retention_days_videos' => 7, // 7 days retention
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        $reportType->reportTypeFields()->create([
            'label' => 'Video',
            'name' => 'video_bukti',
            'type' => 'video',
            'order' => 1,
        ]);

        // 2. Create Old Report (Expired)
        $oldVideo = 'reports/old_video.mp4';
        Storage::disk('public')->put($oldVideo, 'content');

        $oldReport = Report::create([
            'report_type_id' => $reportType->id,
            'user_id' => User::factory()->create()->id,
            'data' => ['video_bukti' => $oldVideo],
        ]);
        $oldReport->created_at = now()->subDays(8);
        $oldReport->save();

        // 3. Create New Report (Not Expired)
        $newVideo = 'reports/new_video.mp4';
        Storage::disk('public')->put($newVideo, 'content');

        $newReport = Report::create([
            'report_type_id' => $reportType->id,
            'user_id' => User::factory()->create()->id,
            'data' => ['video_bukti' => $newVideo],
        ]);
        $newReport->created_at = now()->subDays(2);
        $newReport->save();

        // 4. Run Command
        $this->artisan('media:delete-expired')
            ->assertExitCode(0);

        // 5. Assertions
        Storage::disk('public')->assertMissing($oldVideo);
        Storage::disk('public')->assertExists($newVideo);

        $this->assertNull($oldReport->fresh()->data['video_bukti']);
        $this->assertEquals($newVideo, $newReport->fresh()->data['video_bukti']);
    }

    public function test_it_deletes_expired_attendance_photos()
    {
        // 1. Setup Global Retention Setting
        Setting::create([
            'key' => 'attendance_retention_days',
            'value' => 60, // 60 days retention
        ]);

        // 2. Create Old Attendance (Expired)
        $oldPhotoIn = 'attendance/old_in.jpg';
        $oldPhotoOut = 'attendance/old_out.jpg';
        Storage::disk('public')->put($oldPhotoIn, 'content');
        Storage::disk('public')->put($oldPhotoOut, 'content');

        $oldAttendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'photo_in_path' => $oldPhotoIn,
            'photo_out_path' => $oldPhotoOut,
            'date' => now()->subDays(61)->toDateString(),
            'status' => 'present',
        ]);
        $oldAttendance->created_at = now()->subDays(61);
        $oldAttendance->save();

        // 3. Create New Attendance (Not Expired)
        $newPhotoIn = 'attendance/new_in.jpg';
        Storage::disk('public')->put($newPhotoIn, 'content');

        $newAttendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'photo_in_path' => $newPhotoIn,
            'date' => now()->subDays(30)->toDateString(),
            'status' => 'present',
        ]);
        $newAttendance->created_at = now()->subDays(30);
        $newAttendance->save();

        // 4. Run Command
        $this->artisan('media:delete-expired')
            ->assertExitCode(0);

        // 5. Assertions
        Storage::disk('public')->assertMissing($oldPhotoIn);
        Storage::disk('public')->assertMissing($oldPhotoOut);
        Storage::disk('public')->assertExists($newPhotoIn);

        $this->assertNull($oldAttendance->fresh()->photo_in_path);
        $this->assertNull($oldAttendance->fresh()->photo_out_path);
        $this->assertEquals($newPhotoIn, $newAttendance->fresh()->photo_in_path);
    }
}
