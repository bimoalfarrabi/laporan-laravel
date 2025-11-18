@if ($reports->isEmpty())
    <div class="text-center py-10">
        <p class="text-gray-500">Tidak ada laporan yang ditemukan.</p>
    </div>
@else
    {{-- Table View for Larger Screens --}}
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
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
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('reports.index', array_merge(request()->query(), [
                                'sort_by' => $column,
                                'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc',
                            ])) }}">
                                {{ $title }}
                                @if ($sortBy == $column)
                                    @if ($sortDirection == 'asc')
                                        <span>&#9650;</span>
                                    @else
                                        <span>&#9660;</span>
                                    @endif
                                @endif
                            </a>
                        </th>
                    @endforeach

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($reports as $report)
                    <tr>
                        <td class="px-6 py-4">
                            {{ $report->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $report->reportType?->name ?? 'Jenis Laporan Dihapus' }}</div>
                            @if (isset($report->data['deskripsi']))
                                <div class="prose max-w-none text-sm text-gray-500 mt-1">@markdown_limit($report->data['deskripsi'], 100)</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $report->user?->name ?? 'Pengguna Dihapus' }}
                            @if ($report->user?->roles->isNotEmpty())
                                <span
                                    class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
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
                        <td class="px-6 py-4">
                            <x-waktu-dibuat :date="$report->created_at" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('reports.show', $report->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 mr-2">Lihat</a>
                            @can('update', $report)
                                <a href="{{ route('reports.edit', $report->id) }}"
                                    class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                            @endcan
                            @can('delete', $report)
                                <form action="{{ route('reports.destroy', $report->id) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                        data-confirm-dialog="true"
                                        data-swal-title="Hapus Laporan?"
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
            <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-bold text-lg text-gray-800">#{{ $report->id }}</div>
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
                    <span class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }} text-xs">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
                <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                    <p><strong class="text-gray-600">Jenis Laporan:</strong> {{ $report->reportType?->name ?? 'Jenis Laporan Dihapus' }}</p>
                    <p><strong class="text-gray-600">Dibuat Oleh:</strong> {{ $report->user?->name ?? 'Pengguna Dihapus' }} @if ($report->user?->roles->isNotEmpty())<span class="text-xs text-gray-500">({{ $report->user?->roles->first()->name }})</span>@endif</p>
                    <p><strong class="text-gray-600">Waktu Dibuat:</strong> <x-waktu-dibuat :date="$report->created_at" /></p>
                    @if (isset($report->data['deskripsi']))
                        <div class="prose max-w-none text-sm text-gray-500 mt-1">
                            <strong class="text-gray-600">Deskripsi:</strong> @markdown_limit($report->data['deskripsi'], 100)
                        </div>
                    @endif
                </div>
                <div class="mt-3 flex justify-end space-x-2 text-sm">
                    <a href="{{ route('reports.show', $report->id) }}"
                        class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                    @can('update', $report)
                        <a href="{{ route('reports.edit', $report->id) }}"
                            class="text-blue-600 hover:text-blue-900">Edit</a>
                    @endcan
                    @can('delete', $report)
                        <form action="{{ route('reports.destroy', $report->id) }}"
                            method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900"
                                data-confirm-dialog="true"
                                data-swal-title="Hapus Laporan?"
                                data-swal-text="Laporan akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
@endif
