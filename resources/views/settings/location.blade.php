<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Lokasi Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('settings.location.update') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <!-- Latitude -->
                                <div class="mb-4">
                                    <x-input-label for="center_latitude" :value="__('Latitude Titik Pusat')" />
                                    <x-text-input id="center_latitude" class="block mt-1 w-full" type="text" name="center_latitude" :value="old('center_latitude', $settings['center_latitude'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('center_latitude')" class="mt-2" />
                                </div>

                                <!-- Longitude -->
                                <div class="mb-4">
                                    <x-input-label for="center_longitude" :value="__('Longitude Titik Pusat')" />
                                    <x-text-input id="center_longitude" class="block mt-1 w-full" type="text" name="center_longitude" :value="old('center_longitude', $settings['center_longitude'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('center_longitude')" class="mt-2" />
                                </div>

                                <!-- Radius -->
                                <div class="mb-4">
                                    <x-input-label for="allowed_radius_meters" :value="__('Radius yang Diizinkan (dalam meter)')" />
                                    <x-text-input id="allowed_radius_meters" class="block mt-1 w-full" type="number" name="allowed_radius_meters" :value="old('allowed_radius_meters', $settings['allowed_radius_meters'] ?? '100')" required />
                                    <x-input-error :messages="$errors->get('allowed_radius_meters')" class="mt-2" />
                                </div>
                                
                                <div class="flex items-center justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Simpan Pengaturan') }}
                                    </x-primary-button>
                                </div>
                            </div>
                            <div>
                                <!-- Map -->
                                <x-input-label for="map" :value="__('Peta Radius Lokasi')" />
                                <div id="map" style="height: 400px;" class="mt-1 block w-full rounded-md border-gray-300"></div>
                                <p class="mt-2 text-sm text-gray-600">Klik pada peta untuk mengatur Latitude & Longitude.</p>
                            </div>
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
            const latInput = document.getElementById('center_latitude');
            const lonInput = document.getElementById('center_longitude');
            const radiusInput = document.getElementById('allowed_radius_meters');
            const mapDiv = document.getElementById('map');

            // Default location (e.g., Jakarta) if no settings are available
            const initialLat = parseFloat(latInput.value) || -6.2088;
            const initialLon = parseFloat(lonInput.value) || 106.8456;
            const initialRadius = parseInt(radiusInput.value) || 100;

            const map = L.map(mapDiv).setView([initialLat, initialLon], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            let marker = L.marker([initialLat, initialLon]).addTo(map);
            let circle = L.circle([initialLat, initialLon], {
                radius: initialRadius,
                color: 'blue',
                fillColor: '#3388ff',
                fillOpacity: 0.2
            }).addTo(map);

            function updateMap() {
                const lat = parseFloat(latInput.value);
                const lon = parseFloat(lonInput.value);
                const radius = parseInt(radiusInput.value);

                if (!isNaN(lat) && !isNaN(lon) && !isNaN(radius)) {
                    marker.setLatLng([lat, lon]);
                    circle.setLatLng([lat, lon]);
                    circle.setRadius(radius);
                    map.setView([lat, lon], 15);
                }
            }

            // Update map when input fields change
            latInput.addEventListener('input', updateMap);
            lonInput.addEventListener('input', updateMap);
            radiusInput.addEventListener('input', updateMap);

            // Update input fields when map is clicked
            map.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(7);
                const lon = e.latlng.lng.toFixed(7);
                latInput.value = lat;
                lonInput.value = lon;
                updateMap();
            });
        });
    </script>
    @endpush
</x-app-layout>
