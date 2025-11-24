<?php

namespace Tests\Feature;

use App\Models\ReportType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        $roleAnggota = Role::create(['name' => 'anggota']);
        Role::create(['name' => 'danru']);
        Role::create(['name' => 'superadmin']);

        // Create permissions
        $permCreate = \Spatie\Permission\Models\Permission::create(['name' => 'reports:create']);
        
        // Assign permissions to roles
        $roleAnggota->givePermissionTo($permCreate);
    }

    public function test_attendance_photo_upload_success()
    {
        Storage::fake('nextcloud');
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'anggota')->first());

        $this->actingAs($user);

        // Set time to morning shift window (07:00 start, so 07:30 is valid)
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2025-01-01 07:30:00'));

        $response = $this->post(route('attendances.store'), [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'photo' => UploadedFile::fake()->image('attendance.jpg'),
        ]);

        if (session('errors')) {
            dump(session('errors')->all());
        }
        if (session('error')) {
            dump(session('error'));
        }
        dump(session()->all());

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        
        // Assert file was stored
        // Note: The actual path includes timestamp, so we check if *any* file exists in the directory
        $files = Storage::disk('nextcloud')->allFiles('satpam/attendances');
        $this->assertNotEmpty($files);
    }

    public function test_attendance_photo_upload_failure_on_storage_error()
    {
        Storage::fake('nextcloud');
        // Mock the disk to fail on put
        Storage::shouldReceive('disk')->with('nextcloud')->andReturnSelf();
        Storage::shouldReceive('exists')->andReturn(false);
        Storage::shouldReceive('makeDirectory')->andReturn(true);
        Storage::shouldReceive('put')->andReturn(false); // Simulate upload failure

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'anggota')->first());

        $this->actingAs($user);

        $response = $this->post(route('attendances.store'), [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'photo' => UploadedFile::fake()->image('attendance.jpg'),
        ]);

        $response->assertSessionHasErrors(['photo']);
    }

    public function test_report_image_upload_success()
    {
        Storage::fake('nextcloud');
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'anggota')->first());

        $reportType = ReportType::create([
            'name' => 'Laporan Kejadian',
            'is_active' => true,
        ]);
        
        // Create a file field for the report type
        $reportType->reportTypeFields()->create([
            'name' => 'foto_kejadian',
            'label' => 'Foto Kejadian',
            'type' => 'file',
            'required' => true,
            'order' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('reports.store'), [
            'report_type_id' => $reportType->id,
            'foto_kejadian' => [UploadedFile::fake()->image('evidence.jpg')],
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('success');

        $files = Storage::disk('nextcloud')->allFiles('satpam/reports');
        $this->assertNotEmpty($files);
    }

    public function test_report_video_upload_success()
    {
        Storage::fake('nextcloud');
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'anggota')->first());

        $reportType = ReportType::create([
            'name' => 'Laporan Video',
            'is_active' => true,
        ]);
        
        // Create a video field
        $reportType->reportTypeFields()->create([
            'name' => 'video_kejadian',
            'label' => 'Video Kejadian',
            'type' => 'video',
            'required' => true,
            'order' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('reports.store'), [
            'report_type_id' => $reportType->id,
            'video_kejadian' => UploadedFile::fake()->create('evidence.mp4', 1000),
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('success');

        $files = Storage::disk('nextcloud')->allFiles('satpam/reports');
        $this->assertNotEmpty($files);
    }
}
