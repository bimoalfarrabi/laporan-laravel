@if ($users->isEmpty())
    <p class="mt-4 text-gray-500 dark:text-gray-400">Tidak ada pengguna yang diarsipkan.</p>
@else
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col"
                        class="sticky left-0 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600">
                        ID
                    </th>
                    <th scope="col"
                        class="sticky left-16 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600">
                        Nama
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Username
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Peran (Spatie)
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Waktu Dihapus
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($users as $user)
                    <tr>
                        <td
                            class="sticky left-0 bg-white dark:bg-gray-800 px-6 py-4 whitespace-nowrap border-r dark:border-gray-700 dark:text-gray-100">
                            {{ $user->id }}
                        </td>
                        <td
                            class="sticky left-16 bg-white dark:bg-gray-800 px-6 py-4 whitespace-nowrap border-r dark:border-gray-700 dark:text-gray-100">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">
                            {{ $user->username }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">
                            {{ $user->roles->pluck('name')->join(', ') ?: $user->role }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">
                            {{ $user->deleted_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @can('restore', $user)
                                <form action="{{ route('users.restore', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2"
                                        data-confirm-dialog="true" data-swal-title="Pulihkan Pengguna?"
                                        data-swal-text="Pengguna akan dikembalikan ke daftar aktif."
                                        data-swal-icon="info">Pulihkan</button>
                                </form>
                            @endcan
                            @can('forceDelete', $user)
                                <form action="{{ route('users.forceDelete', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        data-confirm-dialog="true" data-swal-title="Hapus Permanen?"
                                        data-swal-text="PERINGATAN: Pengguna akan dihapus selamanya dan tidak dapat dipulihkan!">Hapus
                                        Permanen</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Card View for Small Screens --}}
    <div class="mt-6 sm:hidden space-y-4">
        @foreach ($users as $user)
            <div class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-bold text-lg text-gray-800 dark:text-gray-200">
                        {{ $user->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">#{{ $user->id }}
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                    <p><strong class="text-gray-600 dark:text-gray-400">Username:</strong>
                        {{ $user->username }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Email:</strong>
                        {{ $user->email }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Peran:</strong>
                        {{ $user->roles->pluck('name')->join(', ') ?: $user->role }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dihapus:</strong>
                        {{ $user->deleted_at->format('d-m-Y H:i') }}
                    </p>
                </div>
                <div class="mt-3 flex justify-end space-x-2 text-sm">
                    @can('restore', $user)
                        <form action="{{ route('users.restore', $user->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2"
                                data-confirm-dialog="true" data-swal-title="Pulihkan Pengguna?"
                                data-swal-text="Pengguna akan dikembalikan ke daftar aktif."
                                data-swal-icon="info">Pulihkan</button>
                        </form>
                    @endcan
                    @can('forceDelete', $user)
                        <form action="{{ route('users.forceDelete', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                data-confirm-dialog="true" data-swal-title="Hapus Permanen?"
                                data-swal-text="PERINGATAN: Pengguna akan dihapus selamanya dan tidak dapat dipulihkan!">Hapus
                                Permanen</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 pagination">
        {{ $users->links() }}
    </div>
@endif
