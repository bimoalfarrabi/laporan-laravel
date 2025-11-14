<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat user dengan role 'super_admin'
        User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'), // Ganti dengan password yang diinginkan
            'role' => 'superadmin',
        ]);

        // Membuat user dengan role 'danru'
        User::create([
            'name' => 'Danru A',
            'username' => 'danrua',
            'email' => 'danrua@example.com',
            'password' => Hash::make('password123'), // Ganti dengan password yang diinginkan
            'role' => 'danru',
        ]);

        // Membuat user dengan role 'anggota'
        User::create([
            'name' => 'Anggota 1',
            'username' => 'anggota1',
            'email' => 'anggota1@example.com',
            'password' => Hash::make('password123'), // Ganti dengan password yang diinginkan
            'role' => 'anggota',
        ]);

        User::create([
            'name' => 'Anggota 2',
            'username' => 'anggota2',
            'email' => 'anggota2@example.com',
            'password' => Hash::make('password123'), // Ganti dengan password yang diinginkan
            'role' => 'anggota',
        ]);
    }
}
