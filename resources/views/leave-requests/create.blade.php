<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Pengajuan Izin Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('leave-requests.store') }}">
                        @csrf

                        <!-- Leave Type -->
                        <div>
                            <x-input-label for="leave_type" :value="__('Jenis Izin')" />
                            <select id="leave_type" name="leave_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Pilih Jenis Izin</option>
                                <option value="Izin sakit" {{ old('leave_type') == 'Izin sakit' ? 'selected' : '' }}>Izin sakit</option>
                                <option value="Izin pemeriksaan atau perawatan medis" {{ old('leave_type') == 'Izin pemeriksaan atau perawatan medis' ? 'selected' : '' }}>Izin pemeriksaan atau perawatan medis</option>
                                <option value="Izin keluarga inti sakit" {{ old('leave_type') == 'Izin keluarga inti sakit' ? 'selected' : '' }}>Izin keluarga inti sakit</option>
                                <option value="Izin kedukaan/kematian keluarga" {{ old('leave_type') == 'Izin kedukaan/kematian keluarga' ? 'selected' : '' }}>Izin kedukaan/kematian keluarga</option>
                                <option value="Izin mengurus administrasi resmi" {{ old('leave_type') == 'Izin mengurus administrasi resmi' ? 'selected' : '' }}>Izin mengurus administrasi resmi</option>
                                <option value="Izin memenuhi panggilan instansi pemerintah" {{ old('leave_type') == 'Izin memenuhi panggilan instansi pemerintah' ? 'selected' : '' }}>Izin memenuhi panggilan instansi pemerintah</option>
                                <option value="Izin keperluan keluarga mendadak" {{ old('leave_type') == 'Izin keperluan keluarga mendadak' ? 'selected' : '' }}>Izin keperluan keluarga mendadak</option>
                                <option value="Izin menikah" {{ old('leave_type') == 'Izin menikah' ? 'selected' : '' }}>Izin menikah</option>
                            </select>
                            <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
                        </div>

                        <!-- Start Date -->
                        <div class="mt-4">
                            <x-input-label for="start_date" :value="__('Tanggal Mulai Izin')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <!-- End Date -->
                        <div class="mt-4">
                            <x-input-label for="end_date" :value="__('Tanggal Selesai Izin')" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>

                        <!-- Reason -->
                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Keterangan')" />
                            <textarea id="reason" name="reason" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button type="button" onclick="window.history.back()">
                                {{ __('Batal') }}
                            </x-secondary-button>

                            <x-primary-button class="ms-3">
                                {{ __('Ajukan Izin') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
