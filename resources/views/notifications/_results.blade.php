<div class="space-y-4">
    @forelse($notifications as $notification)
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-indigo-500' }}">
            <div class="flex-1">
                <div class="flex flex-col">
                    @if ($notification->type === 'App\Notifications\NewReportNotification')
                        <span class="font-medium text-lg">{{ $notification->data['user_name'] }} membuat laporan baru</span>
                    @elseif ($notification->type === 'App\Notifications\ReportStatusNotification')
                        <span class="font-medium text-lg">Laporan {{ $notification->data['report_type'] }} Anda <span class="font-bold {{ $notification->data['status'] == 'approved' ? 'text-green-600' : 'text-red-600' }}">{{ $notification->data['status'] }}</span></span>
                    @elseif ($notification->type === 'App\Notifications\LeaveRequestStatusNotification')
                        <span class="font-medium text-lg">Pengajuan Cuti ({{ $notification->data['leave_type'] }}) Anda <span class="font-bold {{ $notification->data['status'] == 'approved' ? 'text-green-600' : 'text-red-600' }}">{{ $notification->data['status'] }}</span></span>
                    @else
                        <span class="font-medium text-lg">Notifikasi Baru</span>
                    @endif
                    <span class="text-sm text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="ml-4">
                @if(!$notification->read_at)
                    <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="text-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-3 py-1 rounded-full transition-colors duration-200">
                        {{ __('Tandai dibaca') }}
                    </a>
                @else
                    <span class="text-xs text-gray-400 italic">Sudah dibaca</span>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            {{ __('Tidak ada notifikasi.') }}
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $notifications->links() }}
</div>
