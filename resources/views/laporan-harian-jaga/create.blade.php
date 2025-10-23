<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Laporan Harian Jaga Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('laporan-harian-jaga.store') }}">
                        @csrf

                        <!-- Tanggal Jaga -->
                        <div>
                            <x-input-label for="tanggal_jaga" :value="__('Tanggal Jaga')" />
                            <input id="tanggal_jaga" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="date"
                                name= "tanggal_jaga" :value="old('tanggal_jaga')" required autofocus />
                            <x-input-error :messages="$errors->get('tanggal_jaga')" class="mt-2" />
                        </div>

                        <!-- Shift -->
                        <div class="mt-4">
                            <x-input-label for="shift" :value="__('Shift')" />
                            <select id="shift" name="shift"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Pilih Shift</option>
                                <option value="Pagi" {{ old('shift') == 'Pagi' ? 'selected' : '' }}>Pagi</option>
                                <option value="Siang" {{ old('shift') == 'Siang' ? 'selected' : '' }}>Siang</option>
                                <option value="Malam" {{ old('shift') == 'Malam' ? 'selected' : '' }}>Malam</option>
                            </select>
                            <x-input-error :messages="$errors->get('shift')" class="mt-2" />
                        </div>

                        <!-- Cuaca -->
                        <div class="mt-4">
                            <x-input-label for="cuaca" :value="__('Cuaca')" />
                            <x-text-input id="cuaca" class="block mt-1 w-full" type="text" name="cuaca"
                                :value="old('cuaca')" />
                            <x-input-error :messages="$errors->get('cuaca')" class="mt-2" />
                        </div>

                        <!-- Kejadian Menonjol -->
                        <div class="mt-4">
                            <x-input-label for="kejadian_menonjol" :value="__('Kejadian Menonjol')" />
                            <textarea id="kejadian_menonjol" name="kejadian_menonjol"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('kejadian_menonjol') }}</textarea>
                            <x-input-error :messages="$errors->get('kejadian_menonjol')" class="mt-2" />
                        </div>

                        <!-- Catatan Serah Terima -->
                        <div class="mt-4">
                            <x-input-label for="catatan_serah_terima" :value="__('Catatan Serah Terima')" />
                            <textarea id="catatan_serah_terima" name="catatan_serah_terima"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan_serah_terima') }}</textarea>
                            <x-input-error :messages="$errors->get('catatan_serah_terima')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Simpan Laporan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
