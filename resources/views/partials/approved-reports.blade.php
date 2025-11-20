<div id="approved-reports-section">
    <h3 class="text-lg font-semibold mb-4">Laporan Lain yang Disetujui</h3>
    @if ($approvedReports->isNotEmpty())
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
                    @foreach ($approvedReports as $report)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->reportType->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</td>
                            <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$report->created_at" /></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Card View for Small Screens --}}
        <div class="mt-6 sm:hidden">
            @foreach ($approvedReports as $report)
                <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200 @if (!$loop->last) mb-4 @endif">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="font-bold text-lg text-gray-800 mr-2">{{ $report->reportType->name }}</div>
                                                <span class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-green-200 text-green-800 text-xs">
                                                    Disetujui
                                                </span>
                                            </div>                                            <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                        <p><strong class="text-gray-600">Dibuat Oleh:</strong> {{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</p>
                        <p><strong class="text-gray-600">Waktu Dibuat:</b> <x-waktu-dibuat :date="$report->created_at" /></p>
                    </div>
                    <div class="mt-3 flex justify-end space-x-2 text-sm">
                        <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $approvedReports->links() }}
        </div>
    @else
        <p>Tidak ada laporan lain yang disetujui untuk ditampilkan.</p>
    @endif
</div>