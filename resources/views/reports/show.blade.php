<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Laporan: ') . $report->reportType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <strong>ID Laporan:</strong> {{ $report->id }}
                    </div>
                    <div class="mb-4">
                        <strong>Jenis Laporan:</strong> {{ $report->reportType->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Oleh:</strong> {{ $report->user->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Status:</strong>
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
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Pada:</strong> {{ $report->created_at->format('d-m-Y H:i') }}
                    </div>
                    <div class="mb-4">
                        <strong>Terakhir Diperbarui:</strong> {{ $report->updated_at->format('d-m-Y H:i') }}
                    </div>

                    @if ($report->lastEditedBy)
                        <div class="mb-4">
                            <strong>Terakhir Diperbarui Oleh:</strong> {{ $report->lastEditedBy->name }}
                        </div>
                    @endif

                    @if ($report->deleted_at)
                        <div class="mb-4 text-red-600">
                            <strong>Dihapus Oleh:</strong> {{ $report->deletedBy->name ?? 'N/A' }}
                        </div>
                        <div class="mb-4 text-red-600">
                            <strong>Waktu Dihapus:</strong> {{ $report->deleted_at->format('d-m-Y H:i') }}
                        </div>
                    @endif

                    <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-4">Data Laporan:</h3>
                    @foreach ($report->reportType->fields_schema as $field)
                        <div class="mb-2">
                            <strong>{{ $field['label'] }}:</strong>
                            @if ($field['type'] === 'textarea' || $field['type'] === 'text')
                                <p class="whitespace-pre-wrap">{{ $report->data[$field['name']] ?? '-' }}</p>
                            @elseif ($field['type'] === 'date')
                                {{ isset($report->data[$field['name']])
                                    ? Carbon\Carbon::parse($report->data[$field['name']])->format('d-m-Y')
                                    : '-' }}
                            @elseif ($field['type'] === 'time')
                                {{ $report->data[$field['name']] ?? '-' }}
                            @elseif ($field['type'] === 'checkbox')
                                {{ $report->data[$field['name']] ?? false ? 'Ya' : 'Tidak' }}
                            @elseif ($field['type'] === 'file')
                                {{-- Tambahkan kondisi untuk type file --}}
                                @if (isset($report->data[$field['name']]) && $report->data[$field['name']])
                                    <div>
                                        <a href="{{ Storage::url($report->data[$field['name']]) }}" target="_blank"
                                            class="text-blue-600 hover:underline">Lihat File</a>

                                        @php
                                            $extension = pathinfo($report->data[$field['name']], PATHINFO_EXTENSION);
                                        @endphp
                                        @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                            <img src="{{ Storage::url($report->data[$field['name']]) }}"
                                                alt="{{ $field['label'] }}"
                                                class="h-40 w-auto object-cover rounded-md mt-1">
                                        @endif
                                    </div>
                                @else
                                    -
                                @endif
                            @else
                                {{ $report->data[$field['name']] ?? '-' }}
                            @endif
                        </div>
                    @endforeach

                    <div class="flex items-center justify-start mt-6">
                        @if ((Auth::user()->hasRole('danru') || Auth::user()->hasRole('superadmin')) && $report->status == 'belum disetujui')
                            <form action="{{ route('reports.approve', $report->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                    {{ __('Setujui') }}
                                </button>
                            </form>
                            <form action="{{ route('reports.reject', $report->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                    {{ __('Tolak') }}
                                </button>
                            </form>
                        @endif

                        @if ($report->deleted_at)
                            @can('restore', $report)
                                <form action="{{ route('reports.restore', $report->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2"
                                        onclick="return confirm('Apakah Anda yakin ingin memulihkan laporan ini?')">
                                        {{ __('Pulihkan Laporan') }}
                                    </button>
                                </form>
                            @endcan
                            @can('forceDelete', $report)
                                <form action="{{ route('reports.forceDelete', $report->id) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2"
                                        onclick="return confirm('PERINGATAN: Ini akan menghapus laporan secara PERMANEN. Apakah Anda yakin?')">
                                        {{ __('Hapus Permanen') }}
                                    </button>
                                </form>
                            @endcan
                        @else
                            <a href="{{ route('reports.edit', $report->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Edit Laporan') }}
                            </a>
                        @endif
                        <a href="{{ route('reports.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Kembali ke Daftar') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
