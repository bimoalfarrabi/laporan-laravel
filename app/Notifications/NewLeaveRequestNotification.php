<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewLeaveRequestNotification extends Notification
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
            'leave_request_id' => $this->leaveRequest->id,
            'leave_type' => $this->leaveRequest->leave_type,
            'user_name' => $this->leaveRequest->user->name,
            'start_date' => $this->leaveRequest->start_date,
            'created_at' => $this->leaveRequest->created_at,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Pengajuan Izin Baru')
            ->icon('/logo.png')
            ->body($this->leaveRequest->user->name . ' mengajukan izin: ' . $this->leaveRequest->leave_type)
            ->action('Lihat Detail', 'view_leave_request')
            ->data(['url' => route('leave-requests.show', $this->leaveRequest->id)]);
    }
}
