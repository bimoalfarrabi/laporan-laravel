<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class PruneBackupUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-backup-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft deletes backup users that are older than 3 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to prune expired backup users...');

        $threeDaysAgo = Carbon::now()->subDays(3);
        $prunedCount = 0;

        // Find users with the 'backup' role who were created more than 3 days ago
        $expiredUsers = User::role('backup')
            ->where('created_at', '<', $threeDaysAgo)
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No expired backup users to prune.');
            return 0;
        }

        foreach ($expiredUsers as $user) {
            $user->delete(); // This will trigger a soft delete
            $prunedCount++;
            $this->line('Soft deleted user: ' . $user->name . ' (ID: ' . $user->id . ')');
        }

        $this->info('Successfully pruned ' . $prunedCount . ' expired backup user(s).');
        return 0;
    }
}

