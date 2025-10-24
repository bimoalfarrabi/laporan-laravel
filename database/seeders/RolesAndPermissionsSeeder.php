<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
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

        // 1. Buat peran (roles) Spatie
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $danruRole = Role::firstOrCreate(['name' => 'danru']);
        $anggotaRole = Role::firstOrCreate(['name' => 'anggota']);

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
