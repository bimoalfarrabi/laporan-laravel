<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Hak Akses Peran') }}
        </h2>
    </x-slot>

    <div x-data="{
        showCopyModal: false,
        targetRoleId: '',
        targetRoleName: '',
        openCopyModal(id, name) {
            this.targetRoleId = id;
            this.targetRoleName = name.charAt(0).toUpperCase() + name.slice(1);
            this.showCopyModal = true;
            this.$nextTick(() => {
                document.getElementById('copyForm').action = `/role-permissions/${id}/copy`;
                const options = document.getElementById('source_role_id').options;
                for (let i = 0; i < options.length; i++) {
                    options[i].disabled = options[i].value == id;
                }
            });
        },
        closeCopyModal() {
            this.showCopyModal = false;
        }
    }">
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
                                                    <button type="button"
                                                        @click="openCopyModal('{{ $role->id }}', '{{ $role->name }}')"
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2">
                                                        Salin Akses
                                                    </button>
                                                    <a href="{{ route('roles.edit', $role->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2">Edit
                                                        Nama</a>
                                                    <form action="{{ route('roles.destroy', $role->id) }}"
                                                        method="POST" class="inline">
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
                                        <div
                                            class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-1 text-sm">
                                            <p><strong class="text-gray-600 dark:text-gray-400">Waktu Dibuat:</strong>
                                                <x-waktu-dibuat :date="$role->created_at" />
                                            </p>
                                        </div>
                                        <div class="mt-3 flex justify-end space-x-2 text-sm">
                                            <a href="{{ route('role-permissions.edit', $role->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit
                                                Hak Akses</a>
                                            <button type="button"
                                                @click="openCopyModal('{{ $role->id }}', '{{ $role->name }}')"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                Salin Akses
                                            </button>
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

        {{-- Modal Copy Permissions --}}
        <div x-show="showCopyModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCopyModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                    @click="closeCopyModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCopyModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="copyForm" method="POST">
                        @csrf
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-green-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100"
                                        id="modal-title">
                                        Salin Hak Akses ke <span x-text="targetRoleName"></span>
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Pilih peran sumber untuk menyalin hak aksesnya. Hak akses peran tujuan akan
                                            digantikan sepenuhnya.
                                        </p>
                                        <div class="mt-4">
                                            <label for="source_role_id"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Salin
                                                dari</label>
                                            <select id="source_role_id" name="source_role_id"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                @foreach ($roles as $r)
                                                    @if ($r->name !== 'superadmin')
                                                        <option value="{{ $r->id }}">{{ ucfirst($r->name) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Salin
                            </button>
                            <button type="button" @click="closeCopyModal()"
                                class="mt-3 inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
