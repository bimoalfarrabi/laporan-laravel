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
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                                <input type="date" name="date" value="{{ request('date') }}"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    title="Filter Tanggal">

                                <input type="text" name="search" placeholder="Cari nama..."
                                    value="{{ request('search') }}"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                                <a href="{{ route('attendances.index') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </form>
                    </div>
                    {{-- End Form Search dan Filter --}}

                    <div id="attendance-results">
                        @include('attendances._results')
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
            attachModalListeners();
            attachSortableListeners(); // Re-attach listeners for new content
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
                Swal.fire({
                    title: 'Foto Absensi',
                    imageUrl: imageUrl,
                    imageAlt: 'Foto Absensi',
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '50%',
                    imageWidth: 'auto',
                    imageHeight: 'auto',
                    customClass: {
                        image: 'rounded-lg'
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
