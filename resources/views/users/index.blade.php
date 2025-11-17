<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if (Auth::user()->hasRole('danru'))
                {{ __('Manajemen Anggota') }}
            @else
                {{ __('Manajemen Pengguna') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @can('create', App\Models\User::class)
                        <div class="flex items-center mb-4 space-x-4"> {{-- Tambahkan space-x-4 --}}
                            <a href="{{ route('users.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                @if (Auth::user()->hasRole('danru'))
                                    Buat Anggota Baru
                                @else
                                    Buat Pengguna Baru
                                @endif
                            </a>
                            @can('viewAny', App\Models\User::class)
                                {{-- Tombol Arsip Pengguna --}}
                                <a href="{{ route('users.archive') }}"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if (Auth::user()->hasRole('danru'))
                                        Lihat Arsip Anggota
                                    @else
                                        Lihat Arsip Pengguna
                                    @endif
                                </a>
                            @endcan
                        </div>
                    @endcan

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Form Search dan Filter --}}
                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                        <div
                            class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                            <input type="text" name="search" placeholder="Cari nama, username, atau email..."
                                value="{{ $search }}"
                                class="block w-full sm:w-auto flex-grow border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @if (Auth::user()->hasRole('superadmin')) {{-- Hanya SuperAdmin yang bisa filter peran --}}
                                <select name="role"
                                    class="block w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Semua Peran</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ $filterRole == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <x-primary-button type="submit" class="w-full sm:w-auto">
                                {{ __('Filter') }}
                            </x-primary-button>
                            <a href="{{ route('users.index') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                    {{-- End Form Search dan Filter --}}

                    @if ($users->isEmpty())
                        <p class="mt-4">Belum ada pengguna yang ditemukan.</p>
                    @else
                        {{-- Table View for Larger Screens --}}
                        <div class="mt-6 overflow-x-auto hidden sm:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @php
                                            $columns = [
                                                'id' => 'ID',
                                                'name' => 'Nama',
                                                'username' => 'Username',
                                                'email' => 'Email',
                                                'nik' => 'NIK',
                                                'phone_number' => 'No. HP',
                                                'created_at' => 'Waktu Dibuat',
                                                'last_login_at' => 'Terakhir Login',
                                            ];
                                        @endphp

                                        @foreach ($columns as $column => $title)
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ route('users.index', [
                                                    'sort_by' => $column,
                                                    'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc',
                                                    'search' => $search,
                                                    'role' => $filterRole,
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
                                            Peran (Spatie)
                                        </th>

                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="sticky left-0 bg-white px-6 py-4 border-r">
                                                {{ $user->id }}
                                            </td>
                                            <td class="sticky left-16 bg-white px-6 py-4 border-r">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->username }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $user->nik }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $user->phone_number }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $user->roles->pluck('name')->join(', ') }}
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('users.show', $user->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-2">Show
                                                </a>
                                                @can('update', $user)
                                                    <a href="{{ route('users.edit', $user->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 mr-2">Edit
                                                    </a>
                                                @endcan
                                                @can('resetPassword', $user)
                                                    {{-- Tombol Reset Password --}}
                                                    <form action="{{ route('users.resetPassword', $user->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-yellow-600 hover:text-yellow-900 mr-2"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Reset Password?"
                                                            data-swal-text="Password untuk {{ $user->name }} akan direset menjadi '123456'."
                                                            >Reset
                                                            Pass</button>
                                                    </form>
                                                @endcan
                                                @can('delete', $user)
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Hapus Pengguna?"
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
                                <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="font-bold text-lg text-gray-800">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">#{{ $user->id }}</div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-2 space-y-1 text-sm">
                                        <p><strong class="text-gray-600">Username:</strong> {{ $user->username }}</p>
                                        <p><strong class="text-gray-600">Email:</strong> {{ $user->email }}</p>
                                        <p><strong class="text-gray-600">NIK:</strong> {{ $user->nik }}</p>
                                        <p><strong class="text-gray-600">No. HP:</strong> {{ $user->phone_number }}</p>
                                        <p><strong class="text-gray-600">Peran:</strong> {{ $user->roles->pluck('name')->join(', ') }}</p>
                                        <p><strong class="text-gray-600">Waktu Dibuat:</strong> <x-waktu-dibuat :date="$user->created_at" /></p>
                                        <p><strong class="text-gray-600">Terakhir Login:</strong>
                                            @if ($user->last_login_at)
                                                <x-waktu-dibuat :date="$user->last_login_at" />
                                            @else
                                                Belum pernah login
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mt-3 flex justify-end space-x-2 text-sm">
                                        <a href="{{ route('users.show', $user->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Show
                                        </a>
                                        @can('update', $user)
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="text-blue-600 hover:text-blue-900">Edit
                                            </a>
                                        @endcan
                                        @can('resetPassword', $user)
                                            <form action="{{ route('users.resetPassword', $user->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-yellow-600 hover:text-yellow-900"
                                                    data-confirm-dialog="true"
                                                    data-swal-title="Reset Password?"
                                                    data-swal-text="Password untuk {{ $user->name }} akan direset menjadi '123456'."
                                                    >Reset
                                                    Pass</button>
                                            </form>
                                        @endcan
                                        @can('delete', $user)
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    data-confirm-dialog="true"
                                                    data-swal-title="Hapus Pengguna?"
                                                    data-swal-text="Pengguna akan dipindahkan ke arsip. Anda yakin?">Hapus</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>