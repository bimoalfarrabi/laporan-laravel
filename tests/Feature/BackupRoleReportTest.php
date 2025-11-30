<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupRoleReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\BackupRoleSeeder::class);
    }

    public function test_backup_can_view_approved_report()
    {
        $backupUser = User::factory()->create();
        $backupUser->assignRole('backup');

        $anggotaUser = User::factory()->create();
        $anggotaUser->assignRole('anggota');

        $reportType = \App\Models\ReportType::factory()->create();
        \App\Models\ReportTypeField::create([
            'report_type_id' => $reportType->id,
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
            'required' => true,
            'order' => 1,
        ]);
        \App\Models\ReportTypeField::create([
            'report_type_id' => $reportType->id,
            'label' => 'Description',
            'name' => 'description',
            'type' => 'textarea',
            'required' => true,
            'order' => 2,
        ]);

        $report = Report::factory()->create([
            'report_type_id' => $reportType->id,
            'user_id' => $anggotaUser->id,
            'status' => 'disetujui',
            'data' => [
                'title' => 'Approved Report',
                'description' => 'This is an approved report.',
            ],
        ]);

        $response = $this->actingAs($backupUser)->get(route('reports.show', $report));

        $response->assertStatus(200);
        $response->assertSee('Approved Report');
    }
}
