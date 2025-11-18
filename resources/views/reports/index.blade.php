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
                            Lihat Penjelasan
                        </a>

                        @can('exportMonthly', App\Models\Report::class)
                            <a href="{{ route('reports.export') }}"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Export Laporan
                            </a>
                        @endcan
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <form id="filter-form" method="GET" action="{{ route('reports.index') }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-center">
                                <div class="lg:col-span-1">
                                    <input type="text" name="search" placeholder="Cari Jenis/Pembuat Laporan..."
                                        value="{{ $search }}"
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                </div>
                                <div class="lg:col-span-1">
                                    <select name="report_type_id"
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Semua Jenis Laporan</option>
                                        @foreach ($reportTypes as $reportType)
                                            <option value="{{ $reportType->id }}"
                                                {{ $filterReportTypeId == $reportType->id ? 'selected' : '' }}>
                                                {{ $reportType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lg:col-span-1">
                                    <select name="filter_by_status"
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Semua Status</option>
                                        <option value="belum disetujui" {{ $filterByStatus == 'belum disetujui' ? 'selected' : '' }}>Belum Disetujui</option>
                                        <option value="disetujui" {{ $filterByStatus == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="ditolak" {{ $filterByStatus == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                                <div class="lg:col-span-1">
                                    <input type="date" name="filter_date" value="{{ request('filter_date') }}"
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        title="Filter Tanggal">
                                </div>
                                <div class="lg:col-span-1 flex items-center">
                                    <label for="filter_by_user" class="flex items-center">
                                        <input type="checkbox" name="filter_by_user" id="filter_by_user" value="1" {{ $filterByUser ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">Laporan Saya</span>
                                    </label>
                                </div>
                                <div class="lg:col-span-1 flex space-x-2">
                                    <a href="{{ route('reports.index') }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- End Form Search dan Filter --}}

                    <div id="report-results">
                        @include('reports._results')
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filter-form');
    const resultsContainer = document.getElementById('report-results');
    let debounceTimeout;

    function fetchResults(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            // Re-initialize any scripts if necessary, e.g., for modals or confirmation dialogs
            // For now, we just need to re-attach pagination listeners
            attachPaginationListeners();
        })
        .catch(error => console.error('Error fetching results:', error));
    }

    function handleFormChange() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = form.action + '?' + params.toString();
            
            history.pushState(null, '', url);
            fetchResults(url);
        }, 300); // 300ms debounce
    }

    function attachPaginationListeners() {
        resultsContainer.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                history.pushState(null, '', url);
                fetchResults(url);
            });
        });
    }

    // Listen for changes on all form inputs
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', handleFormChange);
        input.addEventListener('change', handleFormChange); // For select and date/checkbox
    });

    // Initial attachment for pagination links
    attachPaginationListeners();

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function () {
        fetchResults(location.href);
    });
});
</script>
@endpush
</x-app-layout>