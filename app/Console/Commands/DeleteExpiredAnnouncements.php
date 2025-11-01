<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteExpiredAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft deletes announcements that have passed their expiration date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredAnnouncements = Announcement::whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now())
            ->whereNull('deleted_at') // Only soft delete if not already soft deleted
            ->get();

        if ($expiredAnnouncements->isEmpty()) {
            $this->info('No expired announcements found.');
            return Command::SUCCESS;
        }

        foreach ($expiredAnnouncements as $announcement) {
            $announcement->delete(); // This performs a soft delete
            $this->info("Announcement '{$announcement->title}' (ID: {$announcement->id}) has been soft-deleted.");
        }

        $this->info('Expired announcements soft-deleted successfully.');

        return Command::SUCCESS;
    }
}
