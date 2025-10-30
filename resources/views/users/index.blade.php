<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna') }}
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
                                Buat Pengguna Baru
                            </a>
                            @can('viewAny', App\Models\User::class)
                                {{-- Tombol Arsip Pengguna --}}
                                <a href="{{ route('users.archive') }}"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Lihat Arsip Pengguna
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
                        <div class="flex flex-wrap items-center space-x-8">
                            <input type="text" name="search" placeholder="Cari nama atau email..."
                                value="{{ $search }}"
                                class="block w-full md:w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @if (Auth::user()->hasRole('superadmin')) {{-- Hanya SuperAdmin yang bisa filter peran --}}
                                <select name="role"
                                    class="block w-full md:w-1/4 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Semua Peran</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ $filterRole == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <x-primary-button type="submit">
                                {{ __('Filter') }}
                            </x-primary-button>
                            <a href="{{ route('users.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                    {{-- End Form Search dan Filter --}}

                    @if ($users->isEmpty())
                        <p class="mt-4">Belum ada pengguna yang ditemukan.</p>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            NIK
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. HP
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Peran (Spatie)
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Dibuat
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
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->nik }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->phone_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-waktu-dibuat :date="$user->created_at" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
