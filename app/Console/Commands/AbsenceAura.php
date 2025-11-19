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
        $last24Hours = now()->subHours(24);
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            $attendance = \App\Models\Attendance::where('user_id', $user->id)
                ->where('created_at', '>=', $last24Hours)
                ->first();

            $leaveRequest = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('status', 'disetujui')
                ->where(function ($query) use ($last24Hours) {
                    $query->where('start_date', '>=', $last24Hours)
                          ->orWhere('end_date', '>=', $last24Hours);
                })
                ->first();

            if (!$attendance && !$leaveRequest) {
                \App\Models\Attendance::create([
                    'user_id' => $user->id,
                    'status' => 'Libur',
                    'type' => 'in',
                    'time_in' => now(), // Set time_in for the new attendance record
                ]);
            }
        }
    }
}
