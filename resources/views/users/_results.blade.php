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
                            'nik' => 'NIK',
                            'phone_number' => 'No. HP',
                            'created_at' => 'Waktu Dibuat',
                            'last_login_at' => 'Terakhir Login',
                            'role' => 'Peran',
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
