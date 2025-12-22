<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Arsip Pengguna (Dihapus)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mb-4">
                        Kembali ke Daftar Pengguna Aktif
                    </a>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <form id="filter-form" method="GET" action="{{ route('users.archive') }}" class="mb-4">
                        <div
                            class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                            <input type="text" name="search" placeholder="Cari nama, username, atau email..."
                                value="{{ $search }}"
                                class="block w-full sm:w-auto flex-grow border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @if (Auth::user()->hasRole('superadmin')) {{-- Hanya SuperAdmin yang bisa filter peran --}}
                                <select name="role"
                                    class="block w-full sm:w-auto border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Semua Peran</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ $filterRole == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <a href="{{ route('users.archive') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:bg-gray-300 dark:focus:bg-gray-500 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                    {{-- End Form Search dan Filter --}}

                    <div id="user-results">
                        @include('users._archive_results')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Scrollbar --}}
    <div id="floating-scrollbar-container"
        class="fixed bottom-0 left-0 z-50 w-full overflow-x-auto bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 hidden">
        <div id="floating-scrollbar-content" class="h-4"></div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('filter-form');
                const resultsContainer = document.getElementById('user-results');
                const floatingScrollbarContainer = document.getElementById('floating-scrollbar-container');
                const floatingScrollbarContent = document.getElementById('floating-scrollbar-content');
                let tableContainer = document.querySelector('#user-results .overflow-x-auto'); // Adjusted selector

                let debounceTimeout;

                function initFloatingScrollbar() {
                    tableContainer = document.querySelector('#user-results .overflow-x-auto'); // Re-fetch
                    if (!tableContainer) {
                        floatingScrollbarContainer.classList.add('hidden');
                        return;
                    }

                    // Sync content width
                    floatingScrollbarContent.style.width = tableContainer.scrollWidth + 'px';

                    // Sync scroll events
                    tableContainer.addEventListener('scroll', function() {
                        floatingScrollbarContainer.scrollLeft = tableContainer.scrollLeft;
                    });

                    floatingScrollbarContainer.addEventListener('scroll', function() {
                        tableContainer.scrollLeft = floatingScrollbarContainer.scrollLeft;
                    });

                    // Initial visibility check
                    checkScrollbarVisibility();
                }

                function checkScrollbarVisibility() {
                    if (!tableContainer) return;

                    const rect = tableContainer.getBoundingClientRect();
                    const isTableBottomVisible = (rect.bottom <= window.innerHeight);
                    const hasHorizontalOverflow = tableContainer.scrollWidth > tableContainer.clientWidth;

                    if (hasHorizontalOverflow && !isTableBottomVisible) {
                        floatingScrollbarContainer.classList.remove('hidden');
                        floatingScrollbarContent.style.width = tableContainer.scrollWidth + 'px';
                    } else {
                        floatingScrollbarContainer.classList.add('hidden');
                    }
                }

                // Window resize and scroll handling for visibility
                window.addEventListener('resize', () => {
                    if (tableContainer) {
                        floatingScrollbarContent.style.width = tableContainer.scrollWidth + 'px';
                        checkScrollbarVisibility();
                    }
                });

                window.addEventListener('scroll', checkScrollbarVisibility);


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
                            initFloatingScrollbar(); // Re-init scrollbar
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
                        link.addEventListener('click', function(e) {
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
                    input.addEventListener('change', handleFormChange); // For select
                });

                // Initial attachment of listeners
                attachPaginationListeners();
                initFloatingScrollbar(); // Init on load

                // Handle back/forward browser buttons
                window.addEventListener('popstate', function() {
                    fetchResults(location.href);
                });
            });
        </script>
    @endpush
</x-app-layout>
