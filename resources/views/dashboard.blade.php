<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div id="dashboard-content" class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('phone-numbers.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Daftar Nomor Telepon
                        </a>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.43.872.95 1.112 1.521.14.332.199.69.162 1.045a1.61 1.61 0 0 1-1.044 1.435M19.179 4.125a23.85 23.85 0 0 1 0 2.535m0 0A23.85 23.85 0 0 0 19.179 4.125" />
                            </svg>
                            Pengumuman Penting
                        </h3>
                        @if ($announcements->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($announcements as $announcement)
                                    <div
                                        class="p-4 bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 dark:border-yellow-700 text-yellow-700 dark:text-yellow-200 shadow-md rounded-lg {{ $announcement->expires_at && $announcement->expires_at->isPast() ? 'opacity-60' : '' }}">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-bold text-lg">{{ $announcement->title }}</h4>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 text-right">
                                                Dibuat oleh {{ $announcement->user->name }} pada <x-waktu-dibuat
                                                    :date="$announcement->created_at" /><br>
                                                @if ($announcement->starts_at)
                                                    Berlaku pada <x-waktu-dibuat :date="$announcement->starts_at" /><br>
                                                @endif
                                                @if ($announcement->expires_at)
                                                    Berakhir pada <x-waktu-dibuat :date="$announcement->expires_at" />
                                                    @if ($announcement->expires_at->isPast())
                                                        <span class="text-red-500 font-semibold">(Kedaluwarsa)</span>
                                                    @endif
                                                @else
                                                    Tidak ada tanggal kedaluwarsa
                                                @endif
                                                @if ($announcement->created_at != $announcement->updated_at)
                                                    (diedit pada <x-waktu-dibuat :date="$announcement->updated_at" />)
                                                @endif
                                            </div>
                                        </div>
                                        <p class="mt-2">{{ $announcement->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>Tidak ada pengumuman saat ini.</p>
                        @endif
                    </div>

                    {{-- DANRU DASHBOARD --}}
                    @if (Auth::user()->hasRole(['danru', 'manajemen']))
                        <div id="reports-for-approval-container">
                            @include('partials.reports-for-approval', [
                                'reportsForApproval' => $reportsForApproval,
                            ])
                        </div>

                        {{-- Leave Requests for Danru --}}
                        @if (Auth::user()->hasRole('danru'))
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-4">Pengajuan Izin Menunggu Persetujuan</h3>
                                @if ($pendingLeaveRequests->isNotEmpty())
                                    {{-- Table View for Larger Screens --}}
                                    <div class="overflow-x-auto hidden sm:block">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Jenis Izin</th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Tanggal Mulai</th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Tanggal Selesai</th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($pendingLeaveRequests as $leaveRequest)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            {{ $leaveRequest->user->name }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            {{ $leaveRequest->leave_type }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            {{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            {{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                                class="text-indigo-600 hover:text-indigo-900">Lihat &
                                                                Proses</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Card View for Small Screens --}}
                                    <div class="mt-6 sm:hidden space-y-4">
                                        @foreach ($pendingLeaveRequests as $leaveRequest)
                                            <div
                                                class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div
                                                        class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                        {{ $leaveRequest->user->name }}</div>
                                                    <span
                                                        class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-800 text-xs">
                                                        Menunggu Persetujuan
                                                    </span>
                                                </div>
                                                <div
                                                    class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                                    <p><strong class="text-gray-600 dark:text-gray-400">Jenis
                                                            Izin:</strong>
                                                        {{ $leaveRequest->leave_type }}</p>
                                                    <p><strong
                                                            class="text-gray-600 dark:text-gray-400">Tanggal:</strong>
                                                        {{ $leaveRequest->start_date->format('d M Y') }} -
                                                        {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                                </div>
                                                <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                    <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                        class="text-indigo-600 hover:text-indigo-900">Lihat & Proses</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>Tidak ada pengajuan izin yang memerlukan persetujuan saat ini.</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-8" id="approved-reports-container">
                            @include('partials.approved-reports', ['approvedReports' => $approvedReports])
                        </div>

                        {{-- Latest Leave Requests for Danru and Manajemen --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">5 Pengajuan Izin Terbaru</h3>
                            @if ($latestLeaveRequests->isNotEmpty())
                                {{-- Table View for Larger Screens --}}
                                <div class="overflow-x-auto hidden sm:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Pemohon</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Jenis Izin</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Mulai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Selesai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Status</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($latestLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') {
                                                                $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            } elseif ($leaveRequest->status == 'disetujui') {
                                                                $statusClass = 'bg-green-200 text-green-800';
                                                            } elseif ($leaveRequest->status == 'ditolak') {
                                                                $statusClass = 'bg-red-200 text-red-800';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                            class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($latestLeaveRequests as $leaveRequest)
                                        <div
                                            class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                    {{ $leaveRequest->user->name }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') {
                                                        $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    } elseif ($leaveRequest->status == 'disetujui') {
                                                        $statusClass = 'bg-green-200 text-green-800';
                                                    } elseif ($leaveRequest->status == 'ditolak') {
                                                        $statusClass = 'bg-red-200 text-red-800';
                                                    }
                                                @endphp
                                                <span
                                                    class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div
                                                class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600 dark:text-gray-400">Jenis Izin:</strong>
                                                    {{ $leaveRequest->leave_type }}</p>
                                                <p><strong class="text-gray-600 dark:text-gray-400">Tanggal:</strong>
                                                    {{ $leaveRequest->start_date->format('d M Y') }} -
                                                    {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Tidak ada pengajuan izin terbaru.</p>
                            @endif
                        </div>
                        {{-- ANGGOTA DASHBOARD --}}
                    @elseif (Auth::user()->hasRole(['anggota', 'backup']))
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">5 Laporan Terakhir Anda</h3>
                            <a href="{{ route('reports.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">Buat
                                Laporan Baru</a>
                        </div>
                        @if ($myRecentReports->isNotEmpty())
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Jenis Laporan</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Waktu Dibuat</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($myRecentReports as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $report->reportType->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat
                                                        :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $bgColor = '';
                                                        if ($report->status == 'belum disetujui') {
                                                            $bgColor = 'bg-yellow-200 text-yellow-800';
                                                        } elseif ($report->status == 'disetujui') {
                                                            $bgColor = 'bg-green-200 text-green-800';
                                                        } elseif ($report->status == 'ditolak') {
                                                            $bgColor = 'bg-red-200 text-red-800';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}"
                                                        class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($myRecentReports as $report)
                                    <div
                                        class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                {{ $report->reportType->name }}</div>
                                            @php
                                                $bgColor = '';
                                                if ($report->status == 'belum disetujui') {
                                                    $bgColor = 'bg-yellow-200 text-yellow-800';
                                                } elseif ($report->status == 'disetujui') {
                                                    $bgColor = 'bg-green-200 text-green-800';
                                                } elseif ($report->status == 'ditolak') {
                                                    $bgColor = 'bg-red-200 text-red-800';
                                                }
                                            @endphp
                                            <span
                                                class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                        <div
                                            class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong>
                                                <x-waktu-dibuat :date="$report->created_at" />
                                            </p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('reports.show', $report->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>Anda belum membuat laporan.</p>
                        @endif

                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Pengajuan Izin Anda</h3>
                            @if ($myLeaveRequests->isNotEmpty())
                                {{-- Table View for Larger Screens --}}
                                <div class="overflow-x-auto hidden sm:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Jenis Izin</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Mulai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Selesai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Status</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($myLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') {
                                                                $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            } elseif ($leaveRequest->status == 'disetujui') {
                                                                $statusClass = 'bg-green-200 text-green-800';
                                                            } elseif ($leaveRequest->status == 'ditolak') {
                                                                $statusClass = 'bg-red-200 text-red-800';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                            class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($myLeaveRequests as $leaveRequest)
                                        <div
                                            class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                    {{ $leaveRequest->leave_type }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') {
                                                        $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    } elseif ($leaveRequest->status == 'disetujui') {
                                                        $statusClass = 'bg-green-200 text-green-800';
                                                    } elseif ($leaveRequest->status == 'ditolak') {
                                                        $statusClass = 'bg-red-200 text-red-800';
                                                    }
                                                @endphp
                                                <span
                                                    class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div
                                                class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600 dark:text-gray-400">Tanggal:</strong>
                                                    {{ $leaveRequest->start_date->format('d M Y') }} -
                                                    {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Anda belum memiliki pengajuan izin.</p>
                            @endif
                        </div>

                        <div class="mt-8" id="approved-reports-container">
                            @include('partials.approved-reports', ['approvedReports' => $approvedReports])
                        </div>

                        {{-- SUPERADMIN DASHBOARD --}}
                    @elseif (Auth::user()->hasRole('superadmin'))
                        <h3 class="text-lg font-semibold mb-4">Statistik Sistem</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow text-gray-900 dark:text-gray-200">
                                <strong>Total Pengguna:</strong>
                                {{ $totalUsers }}
                            </div>
                            <div
                                class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg shadow text-yellow-800 dark:text-yellow-200">
                                <strong>Menunggu
                                    Persetujuan:</strong> {{ $reportStats['belum disetujui'] ?? 0 }}
                            </div>
                            <div
                                class="bg-green-100 dark:bg-green-900 p-4 rounded-lg shadow text-green-800 dark:text-green-200">
                                <strong>Disetujui:</strong>
                                {{ $reportStats['disetujui'] ?? 0 }}
                            </div>
                            <div
                                class="bg-red-100 dark:bg-red-900 p-4 rounded-lg shadow text-red-800 dark:text-red-200">
                                <strong>Ditolak:</strong>
                                {{ $reportStats['ditolak'] ?? 0 }}
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4">5 Laporan Terbaru</h3>
                        @if ($recentReports->isNotEmpty())
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Jenis Laporan</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Dibuat Oleh</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Waktu Dibuat</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($recentReports as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $report->reportType->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}
                                                    @if ($report->user?->roles->isNotEmpty())
                                                        <span
                                                            class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat
                                                        :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $bgColor = '';
                                                        if ($report->status == 'belum disetujui') {
                                                            $bgColor = 'bg-yellow-200 text-yellow-800';
                                                        } elseif ($report->status == 'disetujui') {
                                                            $bgColor = 'bg-green-200 text-green-800';
                                                        } elseif ($report->status == 'ditolak') {
                                                            $bgColor = 'bg-red-200 text-red-800';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}"
                                                        class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($recentReports as $report)
                                    <div
                                        class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                {{ $report->reportType->name }}</div>
                                            @php
                                                $bgColor = '';
                                                if ($report->status == 'belum disetujui') {
                                                    $bgColor = 'bg-yellow-200 text-yellow-800';
                                                } elseif ($report->status == 'disetujui') {
                                                    $bgColor = 'bg-green-200 text-green-800';
                                                } elseif ($report->status == 'ditolak') {
                                                    $bgColor = 'bg-red-200 text-red-800';
                                                }
                                            @endphp
                                            <span
                                                class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                        <div
                                            class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600 dark:text-gray-400">Dibuat Oleh:</strong>
                                                {{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())
                                                    <span
                                                        class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>
                                                @endif
                                            </p>
                                            <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong>
                                                <x-waktu-dibuat :date="$report->created_at" />
                                            </p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('reports.show', $report->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>Tidak ada laporan yang dibuat.</p>
                        @endif

                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">5 Pengajuan Izin Terbaru</h3>
                            @if ($latestLeaveRequests->isNotEmpty())
                                {{-- Table View for Larger Screens --}}
                                <div class="overflow-x-auto hidden sm:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Pemohon</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Jenis Izin</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Mulai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Tanggal Selesai</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Status</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($latestLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        {{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') {
                                                                $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            } elseif ($leaveRequest->status == 'disetujui') {
                                                                $statusClass = 'bg-green-200 text-green-800';
                                                            } elseif ($leaveRequest->status == 'ditolak') {
                                                                $statusClass = 'bg-red-200 text-red-800';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                            class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($latestLeaveRequests as $leaveRequest)
                                        <div
                                            class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                                                    {{ $leaveRequest->user->name }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') {
                                                        $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    } elseif ($leaveRequest->status == 'disetujui') {
                                                        $statusClass = 'bg-green-200 text-green-800';
                                                    } elseif ($leaveRequest->status == 'ditolak') {
                                                        $statusClass = 'bg-red-200 text-red-800';
                                                    }
                                                @endphp
                                                <span
                                                    class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div
                                                class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600 dark:text-gray-400">Jenis
                                                        Izin:</strong>
                                                    {{ $leaveRequest->leave_type }}</p>
                                                <p><strong class="text-gray-600 dark:text-gray-400">Tanggal:</strong>
                                                    {{ $leaveRequest->start_date->format('d M Y') }} -
                                                    {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Tidak ada pengajuan izin terbaru.</p>
                            @endif
                        </div>
                    @else
                        {{ __("You're logged in!") }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // AJAX for Approved Reports
            const approvedReportsContainer = document.getElementById('approved-reports-container');
            if (approvedReportsContainer) {
                approvedReportsContainer.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                        e.preventDefault();
                        let url = e.target.href;
                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.text())
                            .then(html => {
                                approvedReportsContainer.innerHTML = html;
                            })
                            .catch(error => console.warn('Something went wrong.', error));
                    }
                });
            }

            // AJAX for Reports for Approval
            const reportsForApprovalContainer = document.getElementById('reports-for-approval-container');
            if (reportsForApprovalContainer) {
                reportsForApprovalContainer.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                        e.preventDefault();
                        let url = e.target.href;
                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.text())
                            .then(html => {
                                reportsForApprovalContainer.innerHTML = html;
                            })
                            .catch(error => console.warn('Something went wrong.', error));
                    }
                });
            }
        });
    </script>
</x-app-layout>
