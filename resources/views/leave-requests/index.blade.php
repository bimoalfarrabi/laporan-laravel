<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Pengajuan Izin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @can('create', App\Models\LeaveRequest::class)
                        <div class="flex items-center mb-4">
                            <a href="{{ route('leave-requests.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Buat Pengajuan Izin Baru
                            </a>
                        </div>
                    @endcan

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4 mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                        <form id="filter-form" method="GET" action="{{ route('leave-requests.index') }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-center">
                                <input type="text" name="search" placeholder="Cari nama pemohon..."
                                    value="{{ request('search') }}"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                                <select name="status"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-200 ease-in-out">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu persetujuan"
                                        {{ request('status') == 'menunggu persetujuan' ? 'selected' : '' }}>Menunggu
                                        Persetujuan</option>
                                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>
                                        Disetujui</option>
                                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>
                                        Ditolak</option>
                                </select>

                                <a href="{{ route('leave-requests.index') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:bg-gray-300 dark:focus:bg-gray-500 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </form>
                    </div>
                    {{-- End Form Search dan Filter --}}

                    <div id="leave-request-results">
                        @include('leave-requests._results')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('filter-form');
                const resultsContainer = document.getElementById('leave-request-results');
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
                    }, 300);
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

                form.querySelectorAll('input, select').forEach(input => {
                    input.addEventListener('input', handleFormChange);
                    input.addEventListener('change', handleFormChange);
                });

                attachPaginationListeners();

                window.addEventListener('popstate', function() {
                    fetchResults(location.href);
                });
            });
        </script>
    @endpush
</x-app-layout>
