<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\BackupRoleSeeder::class);
    }

    public function test_danru_can_view_backup_users()
    {
        $danru = User::factory()->create();
        $danru->assignRole('danru');

        $backupUser = User::factory()->create();
        $backupUser->assignRole('backup');

        $response = $this->actingAs($danru)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee($backupUser->name);
    }

    public function test_danru_can_create_backup_user()
    {
        $danru = User::factory()->create(['shift' => 'pagi']);
        $danru->assignRole('danru');

        $response = $this->actingAs($danru)->post(route('users.store'), [
            'name' => 'Backup User',
            'username' => 'backupuser',
            'email' => 'backup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'backup',
            'nik' => '1234567890123456',
            'phone_number' => '081234567890',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'backupuser',
            'email' => 'backup@example.com',
        ]);
        $this->assertTrue(User::where('username', 'backupuser')->first()->hasRole('backup'));
    }
}
