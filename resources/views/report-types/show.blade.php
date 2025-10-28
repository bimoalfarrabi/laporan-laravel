<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Jenis Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <strong>ID:</strong> {{ $reportType->id }}
                    </div>
                    <div class="mb-4">
                        <strong>Nama:</strong> {{ $reportType->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Slug:</strong> {{ $reportType->slug }}
                    </div>
                    <div class="mb-4">
                        <strong>Deskripsi:</strong> {{ $reportType->description ?? '-' }}
                    </div>
                    <div class="mb-4">
                        <strong>Aktif:</strong> {{ $reportType->is_active ? 'Ya' : 'Tidak' }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Oleh:</strong> {{ $reportType->createdBy->name ?? 'N/A' }}
                    </div>
                    <div class="mb-4">
                        <strong>Terakhir Diperbarui Oleh:</strong> {{ $reportType->updatedBy->name ?? 'N/A' }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Pada:</strong> {{ $reportType->created_at->format('d-m-Y H:i') }}
                    </div>
                    <div class="mb-4">
                        <strong>Terakhir Diperbarui:</strong> {{ $reportType->updated_at->format('d-m-Y H:i') }}
                    </div>

                    <div class="mb-4">
                        <strong>Skema Field (JSON):</strong>
                        <pre class="bg-gray-100 p-3 rounded-md text-sm overflow-x-auto">{{ json_encode($reportType->fields_schema, JSON_PRETTY_PRINT) }}</pre>
                    </div>

                    <div class="flex items-center justify-start mt-6">
                        @can('update', $reportType)
                            <a href="{{ route('report-types.edit', $reportType->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Edit Jenis Laporan') }}
                            </a>
                        @endcan
                        <a href="{{ route('report-types.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Kembali ke Daftar') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
