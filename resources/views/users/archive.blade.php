<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arsip Pengguna (Dihapus)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Kembali ke Daftar Pengguna Aktif
                    </a>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($users->isEmpty())
                        <p class="mt-4">Tidak ada pengguna yang diarsipkan.</p>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="sticky left-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="sticky left-16 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama
                                        </th>
                                        <th scope="col"
                                            class="sticky left-48 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Username
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Peran (Spatie)
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Dihapus
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
                                            <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap">
                                                {{ $user->id }}
                                            </td>
                                            <td class="sticky left-16 bg-white px-6 py-4 whitespace-nowrap">
                                                {{ $user->name }}
                                            </td>
                                            <td class="sticky left-48 bg-white px-6 py-4 whitespace-nowrap">
                                                {{ $user->username }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $user->deleted_at->format('d-m-Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @can('restore', $user)
                                                    <form action="{{ route('users.restore', $user->id) }}" method="POST"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-green-600 hover:text-green-900 mr-2"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Pulihkan Pengguna?"
                                                            data-swal-text="Pengguna akan dikembalikan ke daftar aktif."
                                                            data-swal-icon="info">Pulihkan</button>
                                                    </form>
                                                @endcan
                                                @can('forceDelete', $user)
                                                    <form action="{{ route('users.forceDelete', $user->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Hapus Permanen?"
                                                            data-swal-text="PERINGATAN: Pengguna akan dihapus selamanya dan tidak dapat dipulihkan!"
                                                            >Hapus
                                                            Permanen</button>
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
