<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Laporan: ') . $report->reportType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Report Metadata --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Informasi Laporan</h3>
                            <div class="mt-4 space-y-2 text-gray-900">
                                <p><strong>ID Laporan:</strong> {{ $report->id }}</p>
                                <p><strong>Jenis Laporan:</strong> {{ $report->reportType->name }}</p>
                                <p><strong>Dibuat Oleh:</strong> {{ $report->user->name }}</p>
                                @if ($report->lastEditedBy)
                                    <p><strong>Terakhir Diperbarui Oleh:</strong> {{ $report->lastEditedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Status & Waktu</h3>
                            <div class="mt-4 space-y-2 text-gray-900">
                                <p><strong>Status:</strong>
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
                                        class="px-2 inline-flex leading-5 font-semibold rounded-full {{ $bgColor }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </p>
                                <p><strong>Dibuat Pada:</strong> {{ $report->created_at->format('d-m-Y H:i') }}</p>
                                <p><strong>Terakhir Diperbarui:</strong> {{ $report->updated_at->format('d-m-Y H:i') }}
                                </p>
                                @if ($report->deleted_at)
                                    <div class="text-red-600">
                                        <p><strong>Dihapus Oleh:</strong> {{ $report->deletedBy->name ?? 'N/A' }}</p>
                                        <p><strong>Waktu Dihapus:</strong>
                                            {{ $report->deleted_at->format('d-m-Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-6">

                    {{-- Report Data --}}
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Data Laporan</h3>
                        <div class="space-y-4">
                            @foreach ($report->reportType->reportTypeFields as $field)
                                <div class="p-4 border rounded-lg">
                                    <strong class="text-gray-600">{{ $field->label }}:</strong>
                                    <div class="mt-1 text-gray-900">
                                        @if ($field->type === 'textarea' || $field->type === 'text')
                                            <p class="whitespace-pre-wrap">{{ $report->data[$field->name] ?? '-' }}
                                            </p>
                                        @elseif ($field->type === 'date')
                                            {{ isset($report->data[$field->name]) ? Carbon\Carbon::parse($report->data[$field->name])->format('d-m-Y') : '-' }}
                                        @elseif ($field->type === 'time')
                                            {{ $report->data[$field->name] ?? '-' }}
                                        @elseif ($field->type === 'checkbox')
                                            <span
                                                class="{{ ($report->data[$field->name] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                                                {{ ($report->data[$field->name] ?? false) ? 'Ya' : 'Tidak' }}
                                            </span>
                                        @elseif ($field->type === 'file')
                                            @if (isset($report->data[$field->name]) && $report->data[$field->name] && Storage::disk('public')->exists($report->data[$field->name]))
                                                <div>
                                                    <a href="{{ Storage::url($report->data[$field->name]) }}"
                                                        target="_blank" class="text-blue-600 hover:underline">Lihat
                                                        File</a>

                                                    @php
                                                        $extension = pathinfo($report->data[$field->name], PATHINFO_EXTENSION);
                                                    @endphp
                                                    @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                                        <img src="{{ Storage::url($report->data[$field->name]) }}"
                                                            alt="{{ $field->label }}"
                                                            class="max-w-xs h-auto object-cover rounded-md mt-2 shadow-md">
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-red-500">foto telah dihapus</p>
                                            @endif
                                        @else
                                            {{ $report->data[$field->name] ?? '-' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end mt-8 pt-6 border-t">
                        <div class="flex items-center space-x-4">
                            @if ($report->deleted_at)
                                @can('restore', $report)
                                    <form action="{{ route('reports.restore', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            onclick="return confirm('Apakah Anda yakin ingin memulihkan laporan ini?')">
                                            {{ __('Pulihkan') }}
                                        </button>
                                    </form>
                                @endcan
                                @can('forceDelete', $report)
                                    <form action="{{ route('reports.forceDelete', $report->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            onclick="return confirm('PERINGATAN: Ini akan menghapus laporan secara PERMANEN. Apakah Anda yakin?')">
                                            {{ __('Hapus Permanen') }}
                                        </button>
                                    </form>
                                @endcan
                            @else
                                @if (($report->status == 'belum disetujui') && (Auth::user()->can('reports:approve') || Auth::user()->can('reports:reject')))
                                    @can('approve', $report)
                                        <form action="{{ route('reports.approve', $report->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('Setujui') }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('reject', $report)
                                        <form action="{{ route('reports.reject', $report->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                {{ __('Tolak') }}
                                            </button>
                                        </form>
                                    @endcan
                                @endif

                                @can('update', $report)
                                    <a href="{{ route('reports.edit', $report->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Edit') }}
                                    </a>
                                @endcan
                                @can('view', $report) {{-- Use view policy to check if user can export --}}
                                    <a href="{{ route('reports.exportPdf', $report->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Export PDF') }}
                                    </a>
                                @endcan
                            @endif
                            <a href="{{ route('reports.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Kembali') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>