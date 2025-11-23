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
                            <select id="leave_type" name="leave_type"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">Pilih Jenis Izin</option>
                                <option value="Izin sakit" {{ old('leave_type') == 'Izin sakit' ? 'selected' : '' }}>
                                    Izin sakit</option>
                                <option value="Izin pemeriksaan atau perawatan medis"
                                    {{ old('leave_type') == 'Izin pemeriksaan atau perawatan medis' ? 'selected' : '' }}>
                                    Izin pemeriksaan atau perawatan medis</option>
                                <option value="Izin keluarga inti sakit"
                                    {{ old('leave_type') == 'Izin keluarga inti sakit' ? 'selected' : '' }}>Izin
                                    keluarga inti sakit</option>
                                <option value="Izin kedukaan/kematian keluarga"
                                    {{ old('leave_type') == 'Izin kedukaan/kematian keluarga' ? 'selected' : '' }}>Izin
                                    kedukaan/kematian keluarga</option>
                                <option value="Izin mengurus administrasi resmi"
                                    {{ old('leave_type') == 'Izin mengurus administrasi resmi' ? 'selected' : '' }}>Izin
                                    mengurus administrasi resmi</option>
                                <option value="Izin memenuhi panggilan instansi pemerintah"
                                    {{ old('leave_type') == 'Izin memenuhi panggilan instansi pemerintah' ? 'selected' : '' }}>
                                    Izin memenuhi panggilan instansi pemerintah</option>
                                <option value="Izin keperluan keluarga mendadak"
                                    {{ old('leave_type') == 'Izin keperluan keluarga mendadak' ? 'selected' : '' }}>Izin
                                    keperluan keluarga mendadak</option>
                                <option value="Izin menikah"
                                    {{ old('leave_type') == 'Izin menikah' ? 'selected' : '' }}>Izin menikah</option>
                                <option value="Izin terlambat"
                                    {{ old('leave_type') == 'Izin terlambat' ? 'selected' : '' }}>Izin terlambat
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
                        </div>

                        <!-- Start Date -->
                        <div class="mt-4">
                            <x-input-label for="start_date" id="start_date_label" :value="__('Tanggal Mulai Izin')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date"
                                :value="old('start_date')" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <!-- End Date -->
                        <div class="mt-4" id="end_date_wrapper">
                            <x-input-label for="end_date" :value="__('Tanggal Selesai Izin')" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date"
                                :value="old('end_date')" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>

                        <div id="time-fields" style="display: none;">
                            <!-- Start Time -->
                            <div class="mt-4">
                                <x-input-label for="start_time" :value="__('Mulai Jam')" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time"
                                    :value="old('start_time')" />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>

                            <!-- End Time -->
                            <div class="mt-4">
                                <x-input-label for="end_time" :value="__('Hingga Jam')" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time"
                                    :value="old('end_time')" />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mt-4">
                            <x-input-label for="keterangan" :value="__('Keterangan')" />
                            <textarea id="keterangan" name="keterangan"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('keterangan') }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const leaveTypeSelect = document.getElementById('leave_type');
                const timeFields = document.getElementById('time-fields');
                const endDateWrapper = document.getElementById('end_date_wrapper');
                const startDateLabel = document.getElementById('start_date_label');

                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');
                const startTimeInput = document.getElementById('start_time');
                const endTimeInput = document.getElementById('end_time');
                const keteranganTextarea = document.getElementById('keterangan');
                const keteranganLabel = document.querySelector('label[for="keterangan"]');

                function toggleFields() {
                    if (leaveTypeSelect.value === 'Izin terlambat') {
                        timeFields.style.display = 'block';
                        endDateWrapper.style.display = 'none';
                        startDateLabel.textContent = 'Tanggal';

                        endDateInput.removeAttribute('required');
                        startTimeInput.setAttribute('required', '');
                        endTimeInput.setAttribute('required', '');
                        keteranganTextarea.setAttribute('required', '');

                        if (keteranganLabel) {
                            keteranganLabel.innerHTML = 'Keterangan <span class="text-red-500">*</span>';
                        }
                    } else {
                        timeFields.style.display = 'none';
                        endDateWrapper.style.display = 'block';
                        startDateLabel.textContent = 'Tanggal Mulai Izin';

                        endDateInput.setAttribute('required', '');
                        startTimeInput.removeAttribute('required');
                        endTimeInput.removeAttribute('required');
                        keteranganTextarea.removeAttribute('required');

                        if (keteranganLabel) {
                            keteranganLabel.innerHTML = 'Keterangan';
                        }
                    }
                }

                leaveTypeSelect.addEventListener('change', toggleFields);

                // Initial check
                toggleFields();
            });
        </script>
    @endpush
</x-app-layout>
