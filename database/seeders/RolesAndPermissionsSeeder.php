<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Tambahkan ini

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Permissions untuk Laporan (Reports)
        $permissions = [
            // Reports
            'reports:view-any', 'reports:view-own', 'reports:create', 'reports:update-any', 'reports:update-own', 'reports:delete-any', 'reports:delete-own', 'reports:restore', 'reports:force-delete',

            // Report Types
            'report-types:view-any', 'report-types:create', 'report-types:update', 'report-types:delete',

            // Users
            'users:view-any', 'users:view', 'users:create', 'users:update', 'users:delete', 'users:restore', 'users:force-delete', 'users:reset-password',

            // Laporan Harian Jaga
            'laporan-harian-jaga:view-any', 'laporan-harian-jaga:view-own', 'laporan-harian-jaga:create', 'laporan-harian-jaga:update-any', 'laporan-harian-jaga:update-own', 'laporan-harian-jaga:delete-any', 'laporan-harian-jaga:delete-own', 'laporan-harian-jaga:restore', 'laporan-harian-jaga:force-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 1. Buat peran (roles) Spatie
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $danruRole = Role::firstOrCreate(['name' => 'danru']);
        $anggotaRole = Role::firstOrCreate(['name' => 'anggota']);

        // Beri hak akses default ke peran
        $danruRole->givePermissionTo([
            // Reports
            'reports:view-any', 'reports:create', 'reports:update-any', 'reports:delete-any', 'reports:restore',
            // Laporan Harian Jaga
            'laporan-harian-jaga:view-any', 'laporan-harian-jaga:create', 'laporan-harian-jaga:update-any', 'laporan-harian-jaga:delete-any', 'laporan-harian-jaga:restore',
            // Users
            'users:view-any', 'users:view',
        ]);

        $anggotaRole->givePermissionTo([
            // Reports
            'reports:view-own', 'reports:create', 'reports:update-own', 'reports:delete-own',
            // Laporan Harian Jaga
            'laporan-harian-jaga:view-own', 'laporan-harian-jaga:create', 'laporan-harian-jaga:update-own', 'laporan-harian-jaga:delete-own',
        ]);


        // 2. Buat Pengguna dan Tugaskan Peran Spatie
        // SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin', // Tetap pertahankan untuk konsistensi atau jika masih ada logika lama yang memakainya
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Danru
        $danru = User::firstOrCreate(
            ['email' => 'danrua@example.com'],
            [
                'name' => 'Danru A',
                'password' => Hash::make('password'),
                'role' => 'danru',
            ]
        );
        $danru->assignRole($danruRole);

        // Anggota 1
        $anggota1 = User::firstOrCreate(
            ['email' => 'anggota1@example.com'],
            [
                'name' => 'Anggota 1',
                'password' => Hash::make('password'),
                'role' => 'anggota',
            ]
        );
        $anggota1->assignRole($anggotaRole);

        // Anggota 2
        $anggota2 = User::firstOrCreate(
            ['email' => 'anggota2@example.com'],
            [
                'name' => 'Anggota 2',
                'password' => Hash::make('password'),
                'role' => 'anggota',
            ]
        );
        $anggota2->assignRole($anggotaRole);


    }
}
