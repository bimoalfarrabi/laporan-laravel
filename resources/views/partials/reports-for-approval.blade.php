<div id="reports-for-approval-section">
    <h3 class="text-lg font-semibold mb-4">Laporan Menunggu Persetujuan</h3>
    @if ($reportsForApproval->isNotEmpty())
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-700" role="alert">
            <p class="font-bold">Perhatian!</p>
            <p>Terdapat {{ $reportsForApproval->count() }} laporan yang menunggu persetujuan Anda.</p>
        </div>
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
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($reportsForApproval as $report)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">{{ $report->reportType->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">{{ $report->user->name }}
                                @if ($report->user?->roles->isNotEmpty())
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400">({{ $report->user?->roles->first()->name }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100"><x-waktu-dibuat
                                    :date="$report->created_at" /></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('reports.show', $report->id) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
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
        <div class="mt-6 sm:hidden grid gap-4">
            @foreach ($reportsForApproval as $report)
                <div
                    class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-bold text-lg text-gray-800 dark:text-gray-200 mr-2">
                            {{ $report->reportType->name }}</div>
                        <span
                            class="flex-shrink-0 px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-yellow-200 text-yellow-800 text-xs">
                            Menunggu Persetujuan
                        </span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                        <p><strong class="text-gray-600 dark:text-gray-400">Dibuat Oleh:</strong>
                            {{ $report->user->name }} @if ($report->user?->roles->isNotEmpty())
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400">({{ $report->user?->roles->first()->name }})</span>
                            @endif
                        </p>
                        <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong> <x-waktu-dibuat
                                :date="$report->created_at" /></p>
                    </div>
                    <div class="mt-3 flex justify-end space-x-2 text-sm">
                        <a href="{{ route('reports.show', $report->id) }}"
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
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

        <div class="mt-4">
            {{ $reportsForApproval->links('pagination.custom') }}
        </div>
    @else
        <p>Tidak ada laporan yang memerlukan persetujuan saat ini.</p>
    @endif
</div>
