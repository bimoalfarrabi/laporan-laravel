<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AbsenceAura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:absence-aura';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as absent if they have no attendance or leave requests in the last 24 hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = now()->subDay()->toDateString();
        $this->info('Checking for inactive users on ' . $yesterday);

        // Get all active users that should be tracked for attendance
        $users = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['anggota', 'danru', 'backup']);
        })->get();

        // Get IDs of users who had an attendance record yesterday
        $usersWithAttendance = \App\Models\Attendance::whereDate('time_in', $yesterday)
            ->pluck('user_id')
            ->unique();

        // Get IDs of users who were on approved leave yesterday
        $usersOnLeave = \App\Models\LeaveRequest::where('status', 'disetujui')
            ->where('start_date', '<=', $yesterday)
            ->where('end_date', '>=', $yesterday)
            ->pluck('user_id')
            ->unique();

        // Combine the IDs of all users who had some form of activity
        $activeUserIds = $usersWithAttendance->merge($usersOnLeave)->unique();

        // Filter the main user list to find those who had no activity
        $inactiveUsers = $users->whereNotIn('id', $activeUserIds);

        if ($inactiveUsers->isEmpty()) {
            $this->info('No inactive users found.');
            return 0;
        }
        
        $this->info('Found ' . $inactiveUsers->count() . ' inactive users.');

        // Create a 'Libur' record for each inactive user for yesterday
        foreach ($inactiveUsers as $user) {
            \App\Models\Attendance::create([
                'user_id' => $user->id,
                'status' => 'Libur',
                'type' => null,
                'time_in' => now()->subDay()->startOfDay(), // Set time_in to start of yesterday
                'created_at' => now()->subDay()->startOfDay(), // Also set created_at to yesterday
                'updated_at' => now()->subDay()->startOfDay(), // Also set updated_at to yesterday
            ]);
            $this->line('Created "Libur" record for user: ' . $user->name);
        }

        $this->info('Successfully created "Libur" records for all inactive users.');
        return 0;
    }
}
