<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Report;
use App\Models\ReportType;

class ReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleUserSeeder']);
    }

    public function test_danru_can_view_approved_report_from_anyone()
    {
        // Create roles
        $roleDanru = Role::where('name', 'danru')->first();
        $roleAnggota = Role::where('name', 'anggota')->first();

        // Create users
        $danru = User::factory()->create();
        $danru->assignRole($roleDanru);

        $anggota = User::factory()->create();
        $anggota->assignRole($roleAnggota);

        // Create a report type
        $reportType = ReportType::factory()->create();

        // Create an approved report by anggota
        $report = Report::factory()->create([
            'user_id' => $anggota->id,
            'report_type_id' => $reportType->id,
            'status' => 'disetujui',
        ]);

        // Act as danru and view the report
        $response = $this->actingAs($danru)->get(route('reports.show', $report));

        // Assert the response is successful
        $response->assertStatus(200);
    }

    public function test_anggota_can_view_approved_report_from_anyone()
    {
        // Create roles
        $roleDanru = Role::where('name', 'danru')->first();
        $roleAnggota = Role::where('name', 'anggota')->first();

        // Create users
        $danru = User::factory()->create();
        $danru->assignRole($roleDanru);

        $anggota = User::factory()->create();
        $anggota->assignRole($roleAnggota);

        // Create a report type
        $reportType = ReportType::factory()->create();

        // Create an approved report by danru
        $report = Report::factory()->create([
            'user_id' => $danru->id,
            'report_type_id' => $reportType->id,
            'status' => 'disetujui',
        ]);

        // Act as anggota and view the report
        $response = $this->actingAs($anggota)->get(route('reports.show', $report));

        // Assert the response is successful
        $response->assertStatus(200);
    }
}
