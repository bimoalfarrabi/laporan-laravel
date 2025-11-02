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
        $role = Role::create(['name' => 'manajemen']);

        $permissions = [
            'reports:view',
            'reports:approve',
            'reports:reject',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $role->givePermissionTo($permissions);
    }
}