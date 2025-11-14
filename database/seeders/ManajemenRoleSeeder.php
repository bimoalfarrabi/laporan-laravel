<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManajemenRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'manajemen']);

        $permissions = [
            'reports:view-any',
            'users:view-any',
            'announcements:view-any',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $role->syncPermissions($permissions);
    }
}