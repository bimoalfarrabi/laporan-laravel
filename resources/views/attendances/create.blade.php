<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absensi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 rounded-lg">
                        @if (!$todayAttendance)
                            <p class="font-bold">Anda akan melakukan Absen Masuk.</p>
                            <p class="text-sm">Pastikan Anda berada di lokasi yang benar dan siap mengambil foto.</p>
                        @elseif (!$todayAttendance->time_out)
                            <p class="font-bold">Anda akan melakukan Absen Pulang.</p>
                            <p class="text-sm">Anda sudah absen masuk pada: {{ \Carbon\Carbon::parse($todayAttendance->time_in)->format('d M Y, H:i') }}</p>
                        @else
                            <p class="font-bold">Anda sudah melakukan Absen Masuk dan Pulang hari ini.</p>
                            <p class="text-sm">Masuk: {{ \Carbon\Carbon::parse($todayAttendance->time_in)->format('d M Y, H:i') }}</p>
                            <p class="text-sm">Pulang: {{ \Carbon\Carbon::parse($todayAttendance->time_out)->format('d M Y, H:i') }}</p>
                            <p class="text-sm">Tipe Absensi: {{ $todayAttendance->type ?? 'N/A' }}</p>
                            <p class="text-sm mt-2">Anda tidak dapat melakukan absensi lagi hari ini.</p>
                        @endif
                    </div>

                    <form action="{{ route('attendances.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- General Location Error -->
                        <x-input-error :messages="$errors->get('location')" class="mt-2 mb-4" />

                        <!-- Photo -->
                        <div class="mb-4">
                            <x-input-label for="photo" :value="__('Ambil Foto Absensi')" />
                            <input type="file" name="photo" id="photo" accept="image/*" capture="environment" required class="mt-1 block w-full" @disabled($todayAttendance && $todayAttendance->time_out)>
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>

                        <!-- Location -->
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        <!-- Map -->
                        <div class="mb-4">
                            <x-input-label for="map" :value="__('Lokasi Anda')" />
                            <div id="map" style="height: 300px;" class="mt-1 block w-full rounded-md border-gray-300"></div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4" @disabled($todayAttendance && $todayAttendance->time_out)>
                                {{ __(!$todayAttendance ? 'Absen Masuk' : (!$todayAttendance->time_out ? 'Absen Pulang' : 'Absensi Selesai')) }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const mapDiv = document.getElementById('map');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    latitudeInput.value = lat;
                    longitudeInput.value = lon;

                    const map = L.map(mapDiv).setView([lat, lon], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('Lokasi Anda saat ini.')
                        .openPopup();

                }, function() {
                    alert('Error: The Geolocation service failed.');
                });
            } else {
                alert("Error: Your browser doesn't support geolocation.");
            }
        });
    </script>
    @endpush
</x-app-layout>
