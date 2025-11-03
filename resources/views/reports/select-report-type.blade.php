<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pilih Jenis Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Jenis Laporan yang Ingin Dibuat:</h3>

                    @if ($reportTypes->isEmpty())
                        <p class="mt-4">Belum ada Jenis Laporan yang aktif. Mohon hubungi SuperAdmin.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($reportTypes as $type)
                                @if ($type->name === 'Laporan Harian Jaga (LHJ) / Shift Report' && !Auth::user()->hasRole('danru'))
                                    @continue
                                @endif
                                <a href="{{ route('reports.create', ['report_type_id' => $type->id]) }}"
                                    class="block p-6 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition duration-150 ease-in-out">
                                    <h4 class="text-xl font-semibold text-gray-900">{{ $type->name }}</h4>
                                    <p class="mt-2 text-gray-600 text-sm">
                                        {{ $type->description ?? 'Tidak ada deskripsi.' }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6">
                        <x-secondary-button onclick="window.history.back()">
                            {{ __('Kembali') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
