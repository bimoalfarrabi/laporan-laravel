<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Laporan Dinamis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4 space-x-4">
                        <a href="{{ route('reports.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
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
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                        <form id="filter-form" method="GET" action="{{ route('reports.index') }}">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-center">
                                <div class="lg:col-span-1">
                                    <input type="text" name="search" placeholder="Cari Jenis/Pembuat Laporan..."
                                        value="{{ $search }}"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                </div>
                                <div class="lg:col-span-1">
                                    <select name="report_type_id"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-200 ease-in-out">
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
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-200 ease-in-out">
                                        <option value="">Semua Status</option>
                                        <option value="belum disetujui"
                                            {{ $filterByStatus == 'belum disetujui' ? 'selected' : '' }}>Belum
                                            Disetujui</option>
                                        <option value="disetujui"
                                            {{ $filterByStatus == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="ditolak" {{ $filterByStatus == 'ditolak' ? 'selected' : '' }}>
                                            Ditolak</option>
                                    </select>
                                </div>
                                <div class="lg:col-span-1">
                                    <input type="date" name="filter_date" value="{{ request('filter_date') }}"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        title="Filter Tanggal">
                                </div>
                                <div class="lg:col-span-1 flex items-center">
                                    <label for="filter_by_user" class="flex items-center">
                                        <input type="checkbox" name="filter_by_user" id="filter_by_user" value="1"
                                            {{ $filterByUser ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Laporan Saya</span>
                                    </label>
                                </div>
                                <div class="lg:col-span-1 flex space-x-2">
                                    <a href="{{ route('reports.index') }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:bg-gray-300 dark:focus:bg-gray-500 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
            document.addEventListener('DOMContentLoaded', function() {
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
                            attachAllListeners();
                        })
                        .catch(error => console.error('Error fetching results:', error));
                }

                function handleFormChange() {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        const formData = new FormData(form);
                        const params = new URLSearchParams(formData);

                        // Preserve sorting parameters
                        const currentUrlParams = new URLSearchParams(window.location.search);
                        if (currentUrlParams.has('sort_by')) {
                            params.set('sort_by', currentUrlParams.get('sort_by'));
                        }
                        if (currentUrlParams.has('sort_direction')) {
                            params.set('sort_direction', currentUrlParams.get('sort_direction'));
                        }

                        const url = form.action + '?' + params.toString();

                        history.pushState(null, '', url);
                        fetchResults(url);
                    }, 300);
                }

                function attachSortableListeners() {
                    resultsContainer.querySelectorAll('thead a').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = this.getAttribute('href');
                            history.pushState(null, '', url);
                            fetchResults(url);
                        });
                    });
                }

                function attachPaginationListeners() {
                    resultsContainer.querySelectorAll('.pagination a').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = this.getAttribute('href');
                            history.pushState(null, '', url);
                            fetchResults(url);
                        });
                    });
                }

                function attachAllListeners() {
                    attachSortableListeners();
                    attachPaginationListeners();
                    // Re-attach listeners for confirmation dialogs if they are dynamically loaded
                    // This assumes you have a function or script that initializes them.
                    if (window.initializeConfirmDialogs) {
                        window.initializeConfirmDialogs();
                    }
                }

                // Listen for changes on all form inputs
                form.querySelectorAll('input, select').forEach(input => {
                    input.addEventListener('input', handleFormChange);
                    input.addEventListener('change', handleFormChange);
                });

                // Initial attachment of listeners
                attachAllListeners();

                // Handle back/forward browser buttons
                window.addEventListener('popstate', function() {
                    fetchResults(location.href);
                });
            });
        </script>
    @endpush
</x-app-layout>
