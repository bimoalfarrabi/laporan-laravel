<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class ReportStatusNotification extends Notification
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
        return ['database', WebPushChannel::class];
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
            'status' => $this->report->status,
            'created_at' => now(),
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $status = ucfirst($this->report->status);
        $body = "Laporan {$this->report->reportType->name} Anda telah {$this->report->status}.";

        return (new WebPushMessage)
            ->title("Status Laporan Diperbarui")
            ->icon('/logo.png')
            ->body($body)
            ->action('Lihat Laporan', 'view_report')
            ->data(['url' => route('reports.show', $this->report->id)]);
    }
}
