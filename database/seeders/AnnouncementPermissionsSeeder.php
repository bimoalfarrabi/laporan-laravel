<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AnnouncementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::firstOrCreate(['name' => 'announcements:create']);
        Permission::firstOrCreate(['name' => 'announcements:update']);
        Permission::firstOrCreate(['name' => 'announcements:delete']);
        Permission::firstOrCreate(['name' => 'announcements:view-any']);
    }
}