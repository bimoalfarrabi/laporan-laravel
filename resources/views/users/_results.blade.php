@if ($users->isEmpty())
    <p class="mt-4 text-gray-500 dark:text-gray-400">Belum ada pengguna yang ditemukan.</p>
@else
    <div class="mt-6 overflow-x-scroll custom-scrollbar hidden sm:block" id="table-container">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-30">
                <tr>
                    @php
                        $columns = [
                            'id' => 'ID',
                            'name' => 'Nama',
                            'username' => 'Username',
                            'nik' => 'NIK',
                            'phone_number' => 'No. HP',
                            'created_at' => 'Waktu Dibuat',
                            'last_login_at' => 'Terakhir Login',
                        ];
                    @endphp

                    @foreach ($columns as $column => $title)
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider {{ $column === 'id' ? 'sticky left-0 z-50 bg-gray-50 dark:bg-gray-700' : '' }} {{ $column === 'name' ? 'sticky left-16 z-50 bg-gray-50 dark:bg-gray-700' : '' }}">
                            <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                class="flex items-center">
                                {{ $title }}
                                @if ($sortBy == $column)
                                    @if ($sortDirection == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                    @endforeach

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Peran
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
                            class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-r dark:border-gray-700 dark:text-gray-100">
                            {{ $user->id }}
                        </td>
                        <td
                            class="sticky left-16 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-r dark:border-gray-700 dark:text-gray-100">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap dark:text-gray-100">
                            {{ $user->username }}
                        </td>
                        <td class="px-6 py-4 dark:text-gray-100">
                            {{ $user->nik }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->phone_number }}
                        </td>
                        <td class="px-6 py-4">
                            <x-waktu-dibuat :date="$user->created_at" />
                        </td>
                        <td class="px-6 py-4">
                            @if ($user->last_login_at)
                                <x-waktu-dibuat :date="$user->last_login_at" />
                            @else
                                Belum pernah login
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->roles->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('users.show', $user->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">Show
                            </a>
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user->id) }}"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2">Edit
                                </a>
                            @endcan
                            @can('resetPassword', $user)
                                {{-- Tombol Reset Password --}}
                                <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 mr-2"
                                        data-confirm-dialog="true" data-swal-title="Reset Password?"
                                        data-swal-text="Password untuk {{ $user->name }} akan direset menjadi '123456'.">Reset
                                        Pass</button>
                                </form>
                            @endcan
                            @can('delete', $user)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        data-confirm-dialog="true" data-swal-title="Hapus Pengguna?"
                                        data-swal-text="Pengguna akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
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
                    <div class="font-bold text-lg text-gray-800 dark:text-gray-200">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">#{{ $user->id }}</div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                    <p><strong class="text-gray-600 dark:text-gray-400">Username:</strong> {{ $user->username }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">NIK:</strong> {{ $user->nik }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">No. HP:</strong> {{ $user->phone_number }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Peran:</strong>
                        {{ $user->roles->pluck('name')->join(', ') }}</p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong> <x-waktu-dibuat
                            :date="$user->created_at" /></p>
                    <p><strong class="text-gray-600 dark:text-gray-400">Terakhir Login:</strong>
                        @if ($user->last_login_at)
                            <x-waktu-dibuat :date="$user->last_login_at" />
                        @else
                            Belum pernah login
                        @endif
                    </p>
                </div>
                <div class="mt-3 flex justify-end space-x-2 text-sm">
                    <a href="{{ route('users.show', $user->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Show
                    </a>
                    @can('update', $user)
                        <a href="{{ route('users.edit', $user->id) }}"
                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Edit
                        </a>
                    @endcan
                    @can('resetPassword', $user)
                        <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                data-confirm-dialog="true" data-swal-title="Reset Password?"
                                data-swal-text="Password untuk {{ $user->name }} akan direset menjadi '123456'.">Reset
                                Pass</button>
                        </form>
                    @endcan
                    @can('delete', $user)
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                data-confirm-dialog="true" data-swal-title="Hapus Pengguna?"
                                data-swal-text="Pengguna akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $users->appends(request()->query())->links('pagination.custom') }}
    </div>
@endif
