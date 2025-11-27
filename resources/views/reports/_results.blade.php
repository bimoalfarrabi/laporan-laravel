@if ($reports->isEmpty())
    <div class="text-center py-10">
        <p class="text-gray-500 dark:text-gray-400">Tidak ada laporan yang ditemukan.</p>
    </div>
@else
    {{-- Table View for Larger Screens --}}
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    @php
                        $columns = [
                            'id' => 'ID',
                            'report_type_name' => 'Jenis Laporan',
                            'user_name' => 'Dibuat Oleh',
                            'status' => 'Status',
                            'created_at' => 'Waktu Dibuat',
                        ];
                    @endphp

                    @foreach ($columns as $column => $title)
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <a href="{{ route(
                                'reports.index',
                                array_merge(request()->query(), [
                                    'sort_by' => $column,
                                    'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc',
                                ]),
                            ) }}"
                                class="flex items-center">
                                {{ $title }}
                                @if ($sortBy == $column)
                                    @if ($sortDirection == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                    @endforeach

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($reports as $report)
                    <tr>
                        <td class="px-6 py-4 dark:text-gray-100">
                            {{ $report->id }}
                        </td>
                        <td class="px-6 py-4 dark:text-gray-100">
                            <div class="font-medium">{{ $report->reportType?->name ?? 'Jenis Laporan Dihapus' }}</div>
                            @if (isset($report->data['deskripsi']))
                                <div class="prose max-w-none text-sm text-gray-500 dark:text-gray-400 mt-1 trix-content"
                                    style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    {!! $report->data['deskripsi'] !!}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 dark:text-gray-100">
                            {{ $report->user?->name ?? 'Pengguna Dihapus' }}
                            @if ($report->user?->roles->isNotEmpty())
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400">({{ $report->user?->roles->first()->name }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 dark:text-gray-100">
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
                            <span class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 dark:text-gray-100">
                            <x-waktu-dibuat :date="$report->created_at" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium dark:text-gray-100">
                            <a href="{{ route('reports.show', $report->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">Lihat</a>
                            @can('update', $report)
                                <a href="{{ route('reports.edit', $report->id) }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2">Edit</a>
                            @endcan
                            @can('delete', $report)
                                <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        data-confirm-dialog="true" data-swal-title="Hapus Laporan?"
                                        data-swal-text="Laporan akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Card View for Small Screens --}}
    <div class="mt-6 sm:hidden space-y-4">
        @foreach ($reports as $report)
            <div class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-bold text-lg text-gray-800 dark:text-gray-200">#{{ $report->id }}</div>
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
                        class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                    <p><strong class="text-gray-600 dark:text-gray-400">Jenis Laporan:</strong>
                        {{ $report->reportType?->name ?? 'Jenis Laporan Dihapus' }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Dibuat Oleh:</strong>
                        {{ $report->user?->name ?? 'Pengguna Dihapus' }} @if ($report->user?->roles->isNotEmpty())
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400">({{ $report->user?->roles->first()->name }})</span>
                        @endif
                    </p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong> <x-waktu-dibuat
                            :date="$report->created_at" /></p>
                    @if (isset($report->data['deskripsi']))
                        <div class="prose max-w-none text-sm text-gray-500 dark:text-gray-400 mt-1 trix-content"
                            style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <strong class="text-gray-600 dark:text-gray-400">Deskripsi:</strong> {!! $report->data['deskripsi'] !!}
                        </div>
                    @endif
                </div>
                <div class="mt-3 flex justify-end space-x-2 text-sm">
                    <a href="{{ route('reports.show', $report->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat</a>
                    @can('update', $report)
                        <a href="{{ route('reports.edit', $report->id) }}"
                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Edit</a>
                    @endcan
                    @can('delete', $report)
                        <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                data-confirm-dialog="true" data-swal-title="Hapus Laporan?"
                                data-swal-text="Laporan akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 pagination">
        {{ $reports->links('pagination.custom') }}
    </div>
@endif
