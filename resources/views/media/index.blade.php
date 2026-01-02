<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Media') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Message -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div x-data="{ activeTab: 'reports' }">
                        <!-- Tabs -->
                        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                                <li class="mr-2" role="presentation">
                                    <button @click="activeTab = 'reports'"
                                        :class="{ 'border-indigo-600 text-indigo-600 dark:text-indigo-500': activeTab === 'reports', 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'reports' }"
                                        class="inline-block p-4 border-b-2 rounded-t-lg" type="button" role="tab">
                                        Media Laporan
                                    </button>
                                </li>
                                <li class="mr-2" role="presentation">
                                    <button @click="activeTab = 'attendance'"
                                        :class="{ 'border-indigo-600 text-indigo-600 dark:text-indigo-500': activeTab === 'attendance', 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'attendance' }"
                                        class="inline-block p-4 border-b-2 rounded-t-lg" type="button" role="tab">
                                        Media Absensi
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Reports Tab Content -->
                        <div x-show="activeTab === 'reports'" class="p-4" role="tabpanel">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Hapus Video & Foto
                                Laporan Lawas</h3>
                            <div class="bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                            Tindakan ini tidak dapat dibatalkan. File yang dihapus akan hilang permanen
                                            dari penyimpanan server.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('media.deleteReports') }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <x-input-label for="report_start_date" :value="__('Tanggal Mulai')" />
                                        <x-text-input id="report_start_date" class="block mt-1 w-full" type="date"
                                            name="start_date" required />
                                    </div>
                                    <div>
                                        <x-input-label for="report_end_date" :value="__('Tanggal Akhir')" />
                                        <x-text-input id="report_end_date" class="block mt-1 w-full" type="date"
                                            name="end_date" required />
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="report_type_id" :value="__('Jenis Laporan (Opsional)')" />
                                    <select id="report_type_id" name="report_type_id"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Semua Jenis Laporan --</option>
                                        @foreach ($reportTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button type="submit" data-confirm-dialog="true"
                                        data-swal-title="Hapus Media Laporan?"
                                        data-swal-text="Tindakan ini tidak dapat dibatalkan! File yang dihapus akan hilang permanen."
                                        data-swal-icon="warning" data-swal-confirm="Ya, Hapus!"
                                        class="bg-red-600 hover:bg-red-700 active:bg-red-800 focus:ring-red-500">
                                        {{ __('Hapus Media Laporan') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>

                        <!-- Attendance Tab Content -->
                        <div x-show="activeTab === 'attendance'" class="p-4" role="tabpanel" style="display: none;">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Hapus Foto Absensi
                                Lawas</h3>
                            <div class="bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                            Tindakan ini tidak dapat dibatalkan. Foto absensi yang dihapus akan hilang
                                            permanen.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('media.deleteAttendance') }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <x-input-label for="attendance_start_date" :value="__('Tanggal Mulai')" />
                                        <x-text-input id="attendance_start_date" class="block mt-1 w-full"
                                            type="date" name="start_date" required />
                                    </div>
                                    <div>
                                        <x-input-label for="attendance_end_date" :value="__('Tanggal Akhir')" />
                                        <x-text-input id="attendance_end_date" class="block mt-1 w-full" type="date"
                                            name="end_date" required />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button type="submit" data-confirm-dialog="true"
                                        data-swal-title="Hapus Foto Absensi?"
                                        data-swal-text="Tindakan ini tidak dapat dibatalkan! Foto yang dihapus akan hilang permanen."
                                        data-swal-icon="warning" data-swal-confirm="Ya, Hapus!"
                                        class="bg-red-600 hover:bg-red-700 active:bg-red-800 focus:ring-red-500">
                                        {{ __('Hapus Foto Absensi') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
