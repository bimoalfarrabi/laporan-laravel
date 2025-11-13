<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Laporan: ') . $report->reportType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Simplified Report Metadata --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="font-semibold text-base sm:text-lg text-gray-800">Informasi Laporan</h3>
                            <div class="mt-4 space-y-2 text-gray-900">
                                <p><strong>ID Laporan:</strong> {{ $report->id }}</p>
                                <p><strong>Jenis Laporan:</strong> {{ $report->reportType->name }}</p>
                                @if ($report->shift)
                                    <p><strong>Shift:</strong> {{ $report->shift }}</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-base sm:text-lg text-gray-800">Status</h3>
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
                            </div>
                        </div>
                    </div>

                    <hr class="my-6">

                    {{-- Report Data (single-column with dividers) --}}
                    <div x-data>
                        <h3 class="font-semibold text-base sm:text-lg text-gray-800 leading-tight mb-4">Data Laporan</h3>
                        @php
                            // Get the value of the 'time' or 'waktu' field to merge it with the 'date' or 'tanggal' field.
                            $timeFieldValue = $report->data['time'] ?? $report->data['waktu'] ?? null;
                            $fields = $report->reportType->reportTypeFields->unique('name')->filter(function ($field) {
                                return !in_array($field->name, ['time', 'waktu']);
                            });
                        @endphp
                        <div class="space-y-6">
                            @foreach ($fields as $field)
                                {{-- Skip rendering the 'time' or 'waktu' field as it's merged with the 'date' or 'tanggal' field --}}
                                <div>
                                    <strong class="text-gray-600">{{ $field->label }}:</strong>
                                    <div class="mt-1 text-gray-900">
                                        @if ($field->name === 'date' || $field->name === 'tanggal')
                                            @php
                                                $dateData = $report->data['date'] ?? $report->data['tanggal'] ?? null;
                                                $dateValue = $dateData ? Carbon\Carbon::parse($dateData)->format('d-m-Y') : null;
                                            @endphp
                                            {{ $dateValue ?? '-' }}
                                            @if ($timeFieldValue)
                                                <span class="ml-2">{{ $timeFieldValue }}</span>
                                            @endif
                                        @elseif ($field->type === 'textarea' || $field->type === 'text')
                                            <p class="whitespace-pre-wrap">{{ $report->data[$field->name] ?? '-' }}</p>
                                        @elseif ($field->type === 'checkbox')
                                            <span
                                                class="{{ $report->data[$field->name] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $report->data[$field->name] ?? false ? 'Ya' : 'Tidak' }}
                                            </span>
                                        @elseif ($field->type === 'file')
                                            @if (!empty($report->data[$field->name]) && Storage::disk('public')->exists($report->data[$field->name]))
                                                @php
                                                    $filePath = $report->data[$field->name];
                                                    $imageUrl = route('files.serve', ['filePath' => $filePath]);
                                                    $isImage = in_array(
                                                        strtolower(pathinfo($filePath, PATHINFO_EXTENSION)),
                                                        ['jpg', 'jpeg', 'png', 'gif', 'svg'],
                                                    );
                                                @endphp

                                                @if ($isImage)
                                                    <a href="#"
                                                        @click.prevent="$dispatch('open-modal', { imageUrl: '{{ $imageUrl }}' })"
                                                        class="block group w-32 h-24 flex-shrink-0">

                                                        <img src="{{ $imageUrl }}" alt="{{ $field->label }}"
                                                            class="w-full h-full object-cover rounded-md mt-2 shadow-md group-hover:opacity-75 transition-opacity">

                                                        <span
                                                            class="text-blue-600 group-hover:underline mt-2 text-sm block">Lihat
                                                            Gambar Penuh</span>

                                                    </a>
                                                @else
                                                    <a href="{{ $imageUrl }}" target="_blank"
                                                        class="text-blue-600 hover:underline">

                                                        Lihat File
                                                        ({{ strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) }})

                                                    </a>
                                                @endif
                                            @else
                                                <p class="text-gray-500">
                                                    @if (!empty($report->data[$field->name]))
                                                        File telah dihapus atau tidak dapat ditemukan.
                                                    @else
                                                        Tidak ada file yang diunggah.
                                                    @endif
                                                </p>
                                            @endif
                                        @else
                                            {{-- Fallback for any other field type --}}
                                            {{ $report->data[$field->name] ?? '-' }}
                                        @endif
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Signature/History Block --}}
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="font-semibold text-base sm:text-lg text-gray-800 mb-4">Riwayat Laporan</h3>
                        <div class="flex flex-col md:flex-row md:justify-between text-sm text-gray-600 space-y-2 md:space-y-0">
                            {{-- Left Side --}}
                            <div class="space-y-2">
                                <p><strong>Dibuat oleh:</strong> {{ $report->user->name }} pada
                                    {{ $report->created_at->format('d-m-Y H:i') }}</p>
                                @if ($report->lastEditedBy && $report->updated_at != $report->created_at)
                                    <p><strong>Terakhir diperbarui oleh:</strong> {{ $report->lastEditedBy->name }} pada
                                        {{ $report->updated_at->format('d-m-Y H:i') }}</p>
                                @endif
                            </div>

                            {{-- Right Side --}}
                            <div class="space-y-2 md:text-right">
                                @if ($report->approvedBy)
                                    <p><strong>Disetujui oleh:</strong> {{ $report->approvedBy->name }} pada
                                        {{ $report->approved_at->format('d-m-Y H:i') }}</p>
                                @endif
                                @if ($report->rejectedBy)
                                    <p><strong>Ditolak oleh:</strong> {{ $report->rejectedBy->name }} pada
                                        {{ $report->rejected_at->format('d-m-Y H:i') }}</p>
                                @endif
                                @if ($report->deleted_at)
                                    <p class="text-red-700"><strong>Dihapus oleh:</strong>
                                        {{ $report->deletedBy?->name ?? 'N/A' }} pada
                                        {{ $report->deleted_at->format('d-m-Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>


                    {{-- Action Buttons --}}
                    <div class="mt-8 pt-6 border-t">
                        <div class="flex flex-wrap items-center justify-start sm:justify-end gap-4">
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
                                @if (
                                    $report->status == 'belum disetujui' &&
                                        (Auth::user()->can('reports:approve') || Auth::user()->can('reports:reject')) &&
                                        Auth::id() !== $report->user_id)
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
                                {{-- @can('view', $report)
                                    <a href="{{ route('reports.exportPdf', $report->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Export PDF') }}
                                    </a>
                                @endcan --}}
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
    <x-image-modal />
</x-app-layout>
