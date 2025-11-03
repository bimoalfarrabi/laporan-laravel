<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Hak Akses untuk Peran: ') . ucfirst($role->name) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('role-permissions.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-10">
                            @if ($role->name === 'danru')
                                <div class="bg-blue-50 rounded-lg p-6 shadow-sm border border-blue-200">
                                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-300 pb-3 mb-4">Hak Akses Tambahan untuk Danru (Otomatis)</h3>
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm text-gray-600">Membuat anggota baru</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm text-gray-600">Menghapus anggota</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm text-gray-600">Melihat semua laporan dari anggota</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm text-gray-600">Menyetujui laporan dari anggota</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-sm text-gray-600">Menolak laporan dari anggota</span>
                                        </div>
                                    </div>
                                    <p class="mt-4 text-xs text-gray-500">Hak akses ini diatur secara otomatis dalam sistem dan tidak dapat diubah dari halaman ini.</p>
                                </div>
                            @endif
                            @foreach ($permissions as $group => $groupPermissions)
                                <div class="bg-gray-50 rounded-lg p-6 shadow-sm border border-gray-200">
                                    <div class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ ucfirst($group) }}</h3>
                                        <div>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="select-all-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" data-group="{{ $group }}">
                                                <span class="ms-2 text-sm font-medium text-gray-700">Select All</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach ($groupPermissions as $permission)
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                    class="permission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    data-group="{{ $group }}"
                                                    {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                <span class="ms-2 text-sm text-gray-600">{{ $permission->name }}</span>
                                                @if ($role->name === 'danru' && in_array($permission->name, ['reports:approve', 'reports:reject', 'reports:view-any', 'reports:export-monthly']))
                                                    <span class="ms-2 text-xs text-blue-500">(Berlaku untuk semua anggota)</span>
                                                @elseif ($role->name === 'anggota' && $permission->name === 'view approved reports')
                                                    <span class="ms-2 text-xs text-blue-500">(Melihat laporan disetujui dari anggota lain)</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end mt-12">
                            <x-primary-button>
                                {{ __('Simpan') }}
                            </x-primary-button>
                            <a href="{{ route('role-permissions.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Batal</a>
                        </div>
                    </form>

                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Logic for Select All
                            document.querySelectorAll('.select-all-checkbox').forEach(masterCheckbox => {
                                masterCheckbox.addEventListener('change', function () {
                                    const group = this.dataset.group;
                                    document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(permissionCheckbox => {
                                        permissionCheckbox.checked = this.checked;
                                    });
                                });
                            });

                            // Logic to update Select All if all children are checked/unchecked
                            document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
                                permissionCheckbox.addEventListener('change', function () {
                                    const group = this.dataset.group;
                                    const allCheckboxesInGroup = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                                    const masterCheckbox = document.querySelector(`.select-all-checkbox[data-group="${group}"]`);
                                    masterCheckbox.checked = Array.from(allCheckboxesInGroup).every(c => c.checked);
                                });
                            });

                            // Set initial state of Select All checkboxes on page load
                            document.querySelectorAll('.select-all-checkbox').forEach(masterCheckbox => {
                                const group = masterCheckbox.dataset.group;
                                const allCheckboxesInGroup = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                                masterCheckbox.checked = Array.from(allCheckboxesInGroup).every(c => c.checked);
                            });
                        });
                    </script>
                    @endpush
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
