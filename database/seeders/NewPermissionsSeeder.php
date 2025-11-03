<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::firstOrCreate(['name' => 'view approved reports']);

        // Create the new permission for monthly export
        $exportMonthlyPermission = Permission::firstOrCreate(['name' => 'reports:export-monthly']);

        // Assign the permission to the 'danru' role
        $danruRole = Role::where('name', 'danru')->first();
        if ($danruRole) {
            $danruRole->givePermissionTo($exportMonthlyPermission);
        }
    }
}