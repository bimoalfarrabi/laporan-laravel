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
                        <div class="mb-4">
                            <input type="text" id="report-type-search" placeholder="Cari jenis laporan..." class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="report-type-grid">
                            @foreach ($reportTypes as $type)
                                @if ($type->name === 'Laporan Harian Jaga (LHJ) / Shift Report' && !Auth::user()->hasRole('danru'))
                                    @continue
                                @endif
                                <a href="{{ route('reports.create', ['report_type_id' => $type->id]) }}"
                                    class="report-type-card block p-6 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition duration-150 ease-in-out">
                                    <h4 class="text-xl font-semibold text-gray-900">{{ $type->name }}</h4>
                                    <p class="mt-2 text-gray-600 text-sm">
                                        {{ $type->description ?? 'Tidak ada deskripsi.' }}</p>
                                </a>
                            @endforeach
                        </div>
                        <div id="no-results-message" class="hidden text-center py-10">
                            <p class="text-gray-500">Tidak ada jenis laporan yang cocok dengan pencarian Anda.</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('report-type-search');
        const reportCards = document.querySelectorAll('.report-type-card');
        const noResultsMessage = document.getElementById('no-results-message');

        searchInput.addEventListener('keyup', function () {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCards = 0;

            reportCards.forEach(function (card) {
                const reportName = card.querySelector('h4').textContent.toLowerCase();
                const reportDescription = card.querySelector('p').textContent.toLowerCase();

                if (reportName.includes(searchTerm) || reportDescription.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    visibleCards++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (visibleCards === 0) {
                noResultsMessage.classList.remove('hidden');
            } else {
                noResultsMessage.classList.add('hidden');
            }
        });
    });
</script>
@endpush

</x-app-layout>
