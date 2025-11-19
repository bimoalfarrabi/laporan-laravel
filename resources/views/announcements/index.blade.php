<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Buat Pengumuman Baru</a>
                            <a href="{{ route('announcements.archive') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Lihat Arsip Pengumuman</a>
                        </div>
                    </div>

                    <div id="announcement-results">
                        @if ($announcements->isEmpty())
                            <p>Belum ada pengumuman.</p>
                        @else
                            {{-- Table View for Larger Screens --}}
                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            @php
                                                $columns = [
                                                    'title' => 'Judul',
                                                    'user_name' => 'Dibuat Oleh',
                                                    'starts_at' => 'Starts At',
                                                    'expires_at' => 'Expires At',
                                                    'created_at' => 'Created At',
                                                    'updated_at' => 'Updated At',
                                                ];
                                            @endphp

                                            @foreach ($columns as $column => $title)
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    <a href="{{ route('announcements.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => ($sortBy == $column && $sortDirection == 'asc') ? 'desc' : 'asc'])) }}" class="flex items-center">
                                                        {{ $title }}
                                                        @if ($sortBy == $column)
                                                            @if ($sortDirection == 'asc')
                                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                            @else
                                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                            @endif
                                                        @endif
                                                    </a>
                                                </th>
                                            @endforeach

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($announcements as $announcement)
                                            <tr class="{{ $announcement->expires_at && $announcement->expires_at->isPast() ? 'bg-red-100 text-red-700' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $announcement->title }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $announcement->user->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($announcement->starts_at)
                                                        <x-waktu-dibuat :date="$announcement->starts_at" />
                                                    @else
                                                        Langsung Aktif
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($announcement->expires_at)
                                                        <x-waktu-dibuat :date="$announcement->expires_at" />
                                                    @else
                                                        Tidak Ada
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$announcement->created_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-waktu-dibuat :date="$announcement->updated_at" /></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('announcements.show', $announcement->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="text-blue-600 hover:text-blue-900 ml-2">Edit</a>
                                                    <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 ml-2"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Hapus Pengumuman?"
                                                            data-swal-text="Pengumuman akan dihapus. Anda yakin?">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Card View for Small Screens --}}
                            <div class="mt-6 sm:hidden space-y-4">
                                @foreach ($announcements as $announcement)
                                    <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200 {{ $announcement->expires_at && $announcement->expires_at->isPast() ? 'bg-red-100 text-red-700' : '' }}">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="font-bold text-lg text-gray-800">{{ $announcement->title }}</div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600">Dibuat Oleh:</strong> {{ $announcement->user->name }}</p>
                                            <p><strong class="text-gray-600">Starts At:</strong>
                                                @if ($announcement->starts_at)
                                                    <x-waktu-dibuat :date="$announcement->starts_at" />
                                                @else
                                                    Langsung Aktif
                                                @endif
                                            </p>
                                            <p><strong class="text-gray-600">Expires At:</strong>
                                                @if ($announcement->expires_at)
                                                    <x-waktu-dibuat :date="$announcement->expires_at" />
                                                @else
                                                    Tidak Ada
                                                @endif
                                            </p>
                                            <p><strong class="text-gray-600">Created At:</strong> <x-waktu-dibuat :date="$announcement->created_at" /></p>
                                            <p><strong class="text-gray-600">Updated At:</strong> <x-waktu-dibuat :date="$announcement->updated_at" /></p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('announcements.show', $announcement->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            <a href="{{ route('announcements.edit', $announcement->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    data-confirm-dialog="true"
                                                    data-swal-title="Hapus Pengumuman?"
                                                    data-swal-text="Pengumuman akan dihapus. Anda yakin?">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $announcements->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resultsContainer = document.getElementById('announcement-results');

    function fetchResults(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newResults = tempDiv.querySelector('#announcement-results');
            if (newResults) {
                resultsContainer.innerHTML = newResults.innerHTML;
            } else {
                resultsContainer.innerHTML = html;
            }
            attachListeners();
        })
        .catch(error => console.error('Error fetching results:', error));
    }

    function attachListeners() {
        // Sortable links
        resultsContainer.querySelectorAll('thead a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                history.pushState(null, '', url);
                fetchResults(url);
            });
        });

        // Pagination links
        resultsContainer.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                history.pushState(null, '', url);
                fetchResults(url);
            });
        });
    }

    attachListeners();

    window.addEventListener('popstate', function () {
        fetchResults(location.href);
    });
});
</script>
@endpush
</x-app-layout>
