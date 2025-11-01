<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserUsernameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            if (empty($user->username)) {
                $username = \Illuminate\Support\Str::slug($user->name);
                $count = \App\Models\User::where('username', 'LIKE', $username . '%')->count();
                if ($count > 0) {
                    $username = $username . ($count + 1);
                }
                $user->username = $username;
                $user->save();
            }
        }
    }
}
