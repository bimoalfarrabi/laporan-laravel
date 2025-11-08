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

                    <form action="{{ route('attendances.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- General Location Error -->
                        <x-input-error :messages="$errors->get('location')" class="mt-2 mb-4" />

                        <!-- Photo -->
                        <div class="mb-4">
                            <x-input-label for="photo" :value="__('Ambil Foto Absensi')" />
                            <input type="file" name="photo" id="photo" accept="image/*" capture="environment" required class="mt-1 block w-full">
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
                            <x-primary-button class="ml-4">
                                {{ __('Kirim Absensi') }}
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
