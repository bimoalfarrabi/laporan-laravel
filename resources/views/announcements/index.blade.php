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
                        <h3 class="text-lg font-semibold">Daftar Pengumuman</h3>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Buat Pengumuman Baru</a>
                            <a href="{{ route('announcements.archive') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Lihat Arsip Pengumuman</a>
                        </div>
                    </div>

                    @if ($announcements->isEmpty())
                        <p>Belum ada pengumuman.</p>
                    @else
                        <div class="overflow-x-auto">
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
                                                <a href="{{ route('announcements.index', [
                                                    'sort_by' => $column,
                                                    'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc',
                                                ]) }}">
                                                    {{ $title }}
                                                    @if ($sortBy == $column)
                                                        @if ($sortDirection == 'asc')
                                                            <span>&#9650;</span>
                                                        @else
                                                            <span>&#9660;</span>
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
                        <div class="mt-4">
                            {{ $announcements->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
