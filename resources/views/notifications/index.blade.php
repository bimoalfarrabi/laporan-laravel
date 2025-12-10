<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Semua Notifikasi') }}
            </h2>
            @if($notifications->count() > 0)
                <a href="{{ route('notifications.markAllAsRead') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    {{ __('Tandai semua sudah dibaca') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="notification-results">
                        @include('notifications._results')
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const resultsContainer = document.getElementById('notification-results');

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

                // Initial attachment of listeners
                attachPaginationListeners();

                // Handle back/forward browser buttons
                window.addEventListener('popstate', function() {
                    fetchResults(location.href);
                });
            });
        </script>
    @endpush
            </div>
        </div>
    </div>
</x-app-layout>
