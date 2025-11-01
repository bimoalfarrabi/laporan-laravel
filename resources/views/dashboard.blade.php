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
                    {{-- DANRU DASHBOARD --}}
                    @if (Auth::user()->hasRole('danru'))
                        <h3 class="text-lg font-semibold mb-4">Laporan Menunggu Persetujuan</h3>
                        @if ($reportsForApproval->isNotEmpty())
                            <div class="overflow-x-auto">
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
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat & Setujui/Tolak</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>Tidak ada laporan yang memerlukan persetujuan saat ini.</p>
                        @endif

                    {{-- ANGGOTA DASHBOARD --}}
                    @elseif (Auth::user()->hasRole('anggota'))
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">5 Laporan Terakhir Anda</h3>
                            <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Buat Laporan Baru</a>
                        </div>
                        @if ($myRecentReports->isNotEmpty())
                            <div class="overflow-x-auto">
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
                        @else
                            <p>Anda belum membuat laporan.</p>
                        @endif

                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Laporan Lain yang Disetujui</h3>
                            @if ($approvedReports->isNotEmpty())
                                <div class="overflow-x-auto">
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
                                            @foreach ($approvedReports as $report)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->reportType->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p>Tidak ada laporan lain yang disetujui untuk ditampilkan.</p>
                            @endif
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
                            <div class="overflow-x-auto">
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
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
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
                        @else
                            <p>Tidak ada laporan yang dibuat.</p>
                        @endif

                    @else
                        {{ __("You're logged in!") }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
