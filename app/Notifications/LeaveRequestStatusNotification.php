<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class LeaveRequestStatusNotification extends Notification
{
    use Queueable;

    protected $leaveRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
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
            'leave_request_id' => $this->leaveRequest->id,
            'leave_type' => $this->leaveRequest->leave_type,
            'status' => $this->leaveRequest->status,
            'created_at' => now(),
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $status = ucfirst($this->leaveRequest->status);
        $body = "Pengajuan Cuti ({$this->leaveRequest->leave_type}) Anda telah {$this->leaveRequest->status}.";

        return (new WebPushMessage)
            ->title("Status Cuti Diperbarui")
            ->icon('/logo.png')
            ->body($body)
            ->action('Lihat Pengajuan', 'view_leave_request')
            ->data(['url' => route('leave-requests.index')]); // Redirect to index as show might not exist or be needed
    }
}
