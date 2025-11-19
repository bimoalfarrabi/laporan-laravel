<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BackupRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the 'anggota' role
        $anggotaRole = Role::where('name', 'anggota')->first();

        if ($anggotaRole) {
            // Get all permissions from the 'anggota' role
            $permissions = $anggotaRole->permissions;

            // Create the 'backup' role
            $backupRole = Role::firstOrCreate(['name' => 'backup']);

            // Assign the same permissions to the 'backup' role
            $backupRole->syncPermissions($permissions);

            $this->command->info('Role "backup" created and synced with "anggota" permissions.');
        } else {
            $this->command->warn('Role "anggota" not found. Could not create "backup" role.');
        }
    }
}
