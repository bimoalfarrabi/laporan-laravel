<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    protected $report;

    /**
     * Create a new notification instance.
     */
    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', \NotificationChannels\WebPush\WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_type' => $this->report->reportType->name,
            'user_name' => $this->report->user->name,
            'created_at' => $this->report->created_at,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new \NotificationChannels\WebPush\WebPushMessage)
            ->title('Laporan Baru!')
            ->icon('/logo.png') // Ensure you have a logo or use a default
            ->body($this->report->user->name . ' membuat laporan baru: ' . $this->report->reportType->name)
            ->action('Lihat Laporan', 'view_report')
            ->data(['url' => route('reports.show', $this->report->id)]);
    }
}
