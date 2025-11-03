<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Laporan Dinamis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center mb-4 space-x-4">
                        <a href="{{ route('reports.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Buat Laporan Baru
                        </a>

                        <a href="{{ route('report-types.explanation') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Lihat Penjelasan Jenis Laporan
                        </a>

                        {{-- @if (Auth::user()->hasRole('danru') || Auth::user()->hasRole('superadmin'))
                            <a href="{{ route('reports.archive') }}"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Lihat Arsip Laporan
                            </a>
                        @endif --}}
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                            <input type="text" name="search" placeholder="Cari Jenis/Pembuat Laporan..."
                                value="{{ $search }}"
                                class="block w-full sm:w-auto flex-grow border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <select name="report_type_id"
                                class="block w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Jenis Laporan</option>
                                @foreach ($reportTypes as $reportType)
                                    <option value="{{ $reportType->id }}"
                                        {{ $filterReportTypeId == $reportType->id ? 'selected' : '' }}>
                                        {{ $reportType->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-primary-button type="submit" class="w-full sm:w-auto">
                                {{ __('Filter') }}
                            </x-primary-button>
                            <a href="{{ route('reports.index') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                    {{-- End Form Search dan Filter --}}

                    @if ($reports->isEmpty())
                        <p class="mt-4">Belum ada Laporan Dinamis yang dibuat.</p>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jenis Laporan
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dibuat Oleh
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Dibuat
                                        </th>
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
                                                <div class="font-medium">{{ $report->reportType->name }}</div>
                                                @if (isset($report->data['deskripsi']))
                                                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($report->data['deskripsi'], 100) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $report->user->name }}
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
