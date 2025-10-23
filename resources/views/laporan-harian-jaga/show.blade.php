<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Laporan Harian Jaga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <strong>ID Laporan:</strong> {{ $laporanHarianJaga->id }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Oleh:</strong> {{ $laporanHarianJaga->user->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Tanggal Jaga:</strong> {{ $laporanHarianJaga->tanggal_jaga->format('d-m-Y') }}
                    </div>
                    <div class="mb-4">
                        <strong>Shift:</strong> {{ $laporanHarianJaga->shift }}
                    </div>
                    <div class="mb-4">
                        <strong>Cuaca:</strong> {{ $laporanHarianJaga->cuaca ?? '-' }}
                    </div>
                    <div class="mb-4">
                        <strong>Kejadian Menonjol:</strong>
                        <p class="whitespace-pre-wrap">{{ $laporanHarianJaga->kejadian_menonjol ?? '-' }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Catatan Serah Terima:</strong>
                        <p class="whitespace-pre-wrap">{{ $laporanHarianJaga->catatan_serah_terima ?? '-' }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Status:</strong> {{ ucfirst($laporanHarianJaga->status) }}
                    </div>
                    <div class="mb-4">
                        <strong>Dibuat Pada:</strong> {{ $laporanHarianJaga->created_at->format('d-m-Y H:i') }}
                    </div>
                    <div class="mb-4">
                        <strong>Terakhir Diperbarui:</strong>
                        {{ $laporanHarianJaga->updated_at->format('d-m-Y H:i') }}
                    </div>

                    <div class="flex items-center justify-start mt-6">
                        <a href="{{ route('laporan-harian-jaga.edit', $laporanHarianJaga->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration- 150 mr-2">
                            {{ __('Edit Laporan') }}
                        </a>
                        <a href="{{ route('laporan-harian-jaga.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Kembali ke Daftar') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
