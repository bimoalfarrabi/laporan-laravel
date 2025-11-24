<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <form id="filter-form" method="GET" action="{{ route('attendances.index') }}">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
                                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    title="Filter Tanggal">

                                <input type="text" name="search" placeholder="Cari nama..."
                                    value="{{ request('search') }}"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                                <div class="flex space-x-2">
                                    <a href="{{ route('attendances.index') }}"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Reset') }}
                                    </a>
                                    @can('export', \App\Models\Attendance::class)
                                        <a href="{{ route('attendances.export') }}"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Export PDF
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- End Form Search dan Filter --}}

                    <div id="attendance-results-container" class="relative">
                        <div id="loading-indicator" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 hidden">
                            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
                        </div>
                        <div id="attendance-results">
                            @include('attendances._results')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filter-form');
    const resultsContainer = document.getElementById('attendance-results');
    const loadingIndicator = document.getElementById('loading-indicator');
    let debounceTimeout;

    function fetchResults(url) {
        loadingIndicator.classList.remove('hidden');
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            attachPaginationListeners();
            attachModalListeners();
            attachSortableListeners(); // Re-attach listeners for new content
        })
        .catch(error => console.error('Error fetching results:', error))
        .finally(() => {
            loadingIndicator.classList.add('hidden');
        });
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

    function attachSortableListeners() {
        resultsContainer.querySelectorAll('thead a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                history.pushState(null, '', url);
                fetchResults(url);
            });
        });
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

    function attachModalListeners() {
        resultsContainer.querySelectorAll('.open-photo-modal').forEach(item => {
            item.addEventListener('click', event => {
                event.preventDefault();
                const imageUrl = event.currentTarget.dataset.fullImageUrl;
                const photoType = event.currentTarget.dataset.photoType; // 'Masuk' or 'Pulang'
                const photoDate = event.currentTarget.dataset.photoDate; // Formatted date
                const photoTime = event.currentTarget.dataset.photoTime; // Formatted time

                let titleText = `Absensi ${photoType}`;
                let htmlContent = `<div class="text-sm text-gray-600">${photoDate}, ${photoTime}</div>`;

                Swal.fire({
                    title: titleText,
                    html: htmlContent,
                    imageAlt: `Foto Absensi ${photoType}`,
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        image: 'rounded-lg',
                        title: 'text-lg md:text-xl',
                        htmlContainer: 'text-sm md:text-base'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                        const imageElement = Swal.getImage();
                        if (imageElement) {
                            imageElement.style.display = 'none'; // Hide until loaded
                            const preloader = new Image();
                            preloader.onload = () => {
                                imageElement.src = imageUrl;
                                imageElement.style.display = 'block';
                                Swal.hideLoading();
                            };
                            preloader.onerror = () => {
                                Swal.showValidationMessage('Gagal memuat gambar.');
                                Swal.hideLoading();
                            };
                            preloader.src = imageUrl;
                        }
                    }
                });
            });
        });
    }

    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', handleFormChange);
        input.addEventListener('change', handleFormChange);
    });

    // Initial attachment of listeners
    attachPaginationListeners();
    attachModalListeners();
    attachSortableListeners();

    window.addEventListener('popstate', function () {
        fetchResults(location.href);
    });
});
</script>
@endpush
</x-app-layout>
