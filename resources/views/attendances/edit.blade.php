<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('attendances.update', $attendance->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- User Name (Read-only) -->
                        <div class="mb-4">
                            <x-input-label for="user_name" :value="__('Nama User')" />
                            <x-text-input id="user_name" class="block mt-1 w-full bg-gray-100 dark:bg-gray-900"
                                type="text" :value="$attendance->user->name" disabled />
                        </div>

                        <!-- Time In -->
                        <div class="mb-4">
                            <x-input-label for="time_in" :value="__('Waktu Masuk')" />
                            <x-text-input id="time_in" class="block mt-1 w-full" type="datetime-local" name="time_in"
                                :value="old(
                                    'time_in',
                                    $attendance->time_in ? $attendance->time_in->format('Y-m-d\TH:i') : '',
                                )" required />
                            <x-input-error :messages="$errors->get('time_in')" class="mt-2" />
                        </div>

                        <!-- Time Out -->
                        <div class="mb-4">
                            <x-input-label for="time_out" :value="__('Waktu Pulang')" />
                            <x-text-input id="time_out" class="block mt-1 w-full" type="datetime-local" name="time_out"
                                :value="old(
                                    'time_out',
                                    $attendance->time_out ? $attendance->time_out->format('Y-m-d\TH:i') : '',
                                )" />
                            <x-input-error :messages="$errors->get('time_out')" class="mt-2" />
                        </div>

                        <!-- Info Box -->
                        <div
                            class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-700 text-blue-700 dark:text-blue-200 rounded-lg">
                            <p class="font-bold">Informasi otentikasi:</p>
                            <p class="text-sm">Status (Tepat Waktu/Terlambat) dan Tipe Shift akan dikalkulasi ulang
                                secara otomatis berdasarkan Waktu Masuk dan Waktu Pulang yang Anda masukkan.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('attendances.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:bg-gray-300 dark:focus:bg-gray-600 active:bg-gray-300 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-2">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
