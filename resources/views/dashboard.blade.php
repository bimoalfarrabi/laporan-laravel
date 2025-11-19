<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('phone-numbers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Daftar Nomor Telepon
                        </a>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.136A1.76 1.76 0 015.882 11H11m0-5.118a1.76 1.76 0 00-3.417-.592l-2.147 6.136A1.76 1.76 0 005.882 13H11m0-7.118l1.559 4.454a1.76 1.76 0 01.592 3.417l-6.136 2.147A1.76 1.76 0 013 15.882V5.882a1.76 1.76 0 011.76-1.76h.002c.636 0 1.21.322 1.559.832l1.441 2.162z" />
                            </svg>
                            Pengumuman Penting
                        </h3>
                        @if ($announcements->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($announcements as $announcement)
                                    <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 shadow-md rounded-lg {{ $announcement->expires_at && $announcement->expires_at->isPast() ? 'opacity-60' : '' }}">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-bold text-lg">{{ $announcement->title }}</h4>
                                            <div class="text-sm text-gray-600 text-right">
                                                Dibuat oleh {{ $announcement->user->name }} pada <x-waktu-dibuat :date="$announcement->created_at" /><br>
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
                        <h3 class="text-lg font-semibold mb-4">Laporan Menunggu Persetujuan</h3>
                        @if ($reportsForApproval->isNotEmpty())
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Laporan</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Dibuat</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($reportsForApproval as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->reportType->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        @if (Auth::id() === $report->user_id)
                                                            Lihat
                                                        @else
                                                            Lihat & Setujui/Tolak
                                                        @endif
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($reportsForApproval as $report)
                                    <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                                                            <div class="flex justify-between items-start mb-2">
                                                                                <div class="font-bold text-lg text-gray-800 mr-2">{{ $report->reportType->name }}</div>
                                                                                <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-800 text-xs">
                                                                                    Menunggu Persetujuan
                                                                                </span>
                                                                            </div>                                        <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600">Dibuat Oleh:</strong> {{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</p>
                                            <p><strong class="text-gray-600">Waktu Dibuat:</strong> <x-waktu-dibuat :date="$report->created_at" /></p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                @if (Auth::id() === $report->user_id)
                                                    Lihat
                                                @else
                                                    Lihat & Setujui/Tolak
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>Tidak ada laporan yang memerlukan persetujuan saat ini.</p>
                        @endif

                        {{-- Leave Requests for Danru --}}
                        @if (Auth::user()->hasRole('danru'))
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-4">Pengajuan Izin Menunggu Persetujuan</h3>
                                @if ($pendingLeaveRequests->isNotEmpty())
                                    {{-- Table View for Larger Screens --}}
                                    <div class="overflow-x-auto hidden sm:block">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($pendingLeaveRequests as $leaveRequest)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->user->name }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leave_type }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat & Proses</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Card View for Small Screens --}}
                                    <div class="mt-6 sm:hidden space-y-4">
                                        @foreach ($pendingLeaveRequests as $leaveRequest)
                                            <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="font-bold text-lg text-gray-800 mr-2">{{ $leaveRequest->user->name }}</div>
                                                    <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-800 text-xs">
                                                        Menunggu Persetujuan
                                                    </span>
                                                </div>
                                                <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                                    <p><strong class="text-gray-600">Jenis Izin:</strong> {{ $leaveRequest->leave_type }}</p>
                                                    <p><strong class="text-gray-600">Tanggal:</strong> {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                                </div>
                                                <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                    <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat & Proses</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>Tidak ada pengajuan izin yang memerlukan persetujuan saat ini.</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-8">
                            @include('partials.approved-reports', ['approvedReports' => $approvedReports])
                        </div>

                        {{-- Latest Leave Requests for Danru and Manajemen --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">5 Pengajuan Izin Terbaru</h3>
                            @if ($latestLeaveRequests->isNotEmpty())
                                {{-- Table View for Larger Screens --}}
                                <div class="overflow-x-auto hidden sm:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($latestLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                            elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                        @endphp
                                                        <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($latestLeaveRequests as $leaveRequest)
                                        <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 mr-2">{{ $leaveRequest->user->name }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                    elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                @endphp
                                                <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600">Jenis Izin:</strong> {{ $leaveRequest->leave_type }}</p>
                                                <p><strong class="text-gray-600">Tanggal:</strong> {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Tidak ada pengajuan izin terbaru.</p>
                            @endif
                        </div>
                    {{-- ANGGOTA DASHBOARD --}}
                    @elseif (Auth::user()->hasRole('anggota'))
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">5 Laporan Terakhir Anda</h3>
                            <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Buat Laporan Baru</a>
                        </div>
                        @if ($myRecentReports->isNotEmpty())
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Laporan</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Dibuat</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($myRecentReports as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->reportType->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $bgColor = '';
                                                        if ($report->status == 'belum disetujui') $bgColor = 'bg-yellow-200 text-yellow-800';
                                                        elseif ($report->status == 'disetujui') $bgColor = 'bg-green-200 text-green-800';
                                                        elseif ($report->status == 'ditolak') $bgColor = 'bg-red-200 text-red-800';
                                                    @endphp
                                                    <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($myRecentReports as $report)
                                    <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="font-bold text-lg text-gray-800 mr-2">{{ $report->reportType->name }}</div>
                                            @php
                                                $bgColor = '';
                                                if ($report->status == 'belum disetujui') $bgColor = 'bg-yellow-200 text-yellow-800';
                                                elseif ($report->status == 'disetujui') $bgColor = 'bg-green-200 text-green-800';
                                                elseif ($report->status == 'ditolak') $bgColor = 'bg-red-200 text-red-800';
                                            @endphp
                                            <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600">Waktu Dibuat:</strong> <x-waktu-dibuat :date="$report->created_at" /></p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
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
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($myLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                            elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                        @endphp
                                                        <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($myLeaveRequests as $leaveRequest)
                                        <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 mr-2">{{ $leaveRequest->leave_type }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                    elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                @endphp
                                                <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600">Tanggal:</strong> {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Anda belum memiliki pengajuan izin.</p>
                            @endif
                        </div>

                        <div class="mt-8">
                            @include('partials.approved-reports', ['approvedReports' => $approvedReports])
                        </div>

                    {{-- SUPERADMIN DASHBOARD --}}
                    @elseif (Auth::user()->hasRole('superadmin'))
                        <h3 class="text-lg font-semibold mb-4">Statistik Sistem</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-white p-4 rounded-lg shadow"><strong>Total Pengguna:</strong> {{ $totalUsers }}</div>
                            <div class="bg-yellow-100 p-4 rounded-lg shadow text-yellow-800"><strong>Menunggu Persetujuan:</strong> {{ $reportStats['belum disetujui'] ?? 0 }}</div>
                            <div class="bg-green-100 p-4 rounded-lg shadow text-green-800"><strong>Disetujui:</strong> {{ $reportStats['disetujui'] ?? 0 }}</div>
                            <div class="bg-red-100 p-4 rounded-lg shadow text-red-800"><strong>Ditolak:</strong> {{ $reportStats['ditolak'] ?? 0 }}</div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4">5 Laporan Terbaru</h3>
                        @if ($recentReports->isNotEmpty())
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Laporan</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Dibuat</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($recentReports as $report)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->reportType->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $bgColor = '';
                                                        if ($report->status == 'belum disetujui') $bgColor = 'bg-yellow-200 text-yellow-800';
                                                        elseif ($report->status == 'disetujui') $bgColor = 'bg-green-200 text-green-800';
                                                        elseif ($report->status == 'ditolak') $bgColor = 'bg-red-200 text-red-800';
                                                    @endphp
                                                    <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">{{ ucfirst($report->status) }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($recentReports as $report)
                                    <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="font-bold text-lg text-gray-800 mr-2">{{ $report->reportType->name }}</div>
                                            @php
                                                $bgColor = '';
                                                if ($report->status == 'belum disetujui') $bgColor = 'bg-yellow-200 text-yellow-800';
                                                elseif ($report->status == 'disetujui') $bgColor = 'bg-green-200 text-green-800';
                                                elseif ($report->status == 'ditolak') $bgColor = 'bg-red-200 text-red-800';
                                            @endphp
                                            <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600">Dibuat Oleh:</strong> {{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</p>
                                            <p><strong class="text-gray-600">Waktu Dibuat:</strong> <x-waktu-dibuat :date="$report->created_at" /></p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
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
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($latestLeaveRequests as $leaveRequest)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->leave_type }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->start_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $leaveRequest->end_date->format('d M Y') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $statusClass = '';
                                                            if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                            elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                            elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                        @endphp
                                                        <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Card View for Small Screens --}}
                                <div class="mt-6 sm:hidden space-y-4">
                                    @foreach ($latestLeaveRequests as $leaveRequest)
                                        <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 mr-2">{{ $leaveRequest->user->name }}</div>
                                                @php
                                                    $statusClass = '';
                                                    if ($leaveRequest->status == 'menunggu persetujuan') $statusClass = 'bg-yellow-200 text-yellow-800';
                                                    elseif ($leaveRequest->status == 'disetujui') $statusClass = 'bg-green-200 text-green-800';
                                                    elseif ($leaveRequest->status == 'ditolak') $statusClass = 'bg-red-200 text-red-800';
                                                @endphp
                                                <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $statusClass }} text-xs">
                                                    {{ ucfirst($leaveRequest->status) }}
                                                </span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                                <p><strong class="text-gray-600">Jenis Izin:</strong> {{ $leaveRequest->leave_type }}</p>
                                                <p><strong class="text-gray-600">Tanggal:</strong> {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="mt-3 flex justify-end space-x-2 text-sm">
                                                <a href="{{ route('leave-requests.show', $leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
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
</x-app-layout>
