<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Hak Akses Peran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4 space-x-4">
                        @if (Auth::user()->hasRole('superadmin'))
                            <a href="{{ route('roles.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Buat Role Baru
                            </a>
                            <a href="{{ route('roles.archive') }}"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 dark:bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 dark:hover:bg-yellow-500 focus:bg-yellow-400 dark:focus:bg-yellow-500 active:bg-yellow-600 dark:active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Lihat Arsip Role
                            </a>
                        @endif
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Table View for Larger Screens --}}
                    <div class="mt-6 overflow-x-auto hidden sm:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @php
                                        $columns = [
                                            'name' => 'Nama Peran',
                                            'created_at' => 'Waktu Dibuat',
                                        ];
                                    @endphp

                                    @foreach ($columns as $column => $title)
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a
                                                href="{{ route('role-permissions.index', [
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
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($roles as $role)
                                    @if ($role->name !== 'superadmin')
                                        <tr>
                                            <td class="px-6 py-4 dark:text-gray-100">{{ ucfirst($role->name) }}</td>
                                            <td class="px-6 py-4 dark:text-gray-100"><x-waktu-dibuat
                                                    :date="$role->created_at" /></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('role-permissions.edit', $role->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">Edit
                                                    Hak
                                                    Akses</a>
                                                <a href="{{ route('roles.edit', $role->id) }}"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2">Edit
                                                    Nama</a>
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        data-confirm-dialog="true" data-swal-title="Hapus Role?"
                                                        data-swal-text="Role akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Card View for Small Screens --}}
                    <div class="mt-6 sm:hidden space-y-4">
                        @foreach ($roles as $role)
                            @if ($role->name !== 'superadmin')
                                <div
                                    class="bg-white dark:bg-gray-800 p-4 shadow-md rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                            {{ ucfirst($role->name) }}</div>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                        <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong>
                                            <x-waktu-dibuat :date="$role->created_at" />
                                        </p>
                                    </div>
                                    <div class="mt-3 flex justify-end space-x-2 text-sm">
                                        <a href="{{ route('role-permissions.edit', $role->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit
                                            Hak Akses</a>
                                        <a href="{{ route('roles.edit', $role->id) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Edit
                                            Nama</a>
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                data-confirm-dialog="true" data-swal-title="Hapus Role?"
                                                data-swal-text="Role akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $roles->appends(request()->query())->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
