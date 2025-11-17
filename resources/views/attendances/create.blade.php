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
                    <div id="attendance-message" class="mb-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 rounded-lg">
                        <!-- Pesan status absensi akan dimuat di sini -->
                    </div>

                    <form id="attendance-form" action="{{ route('attendances.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- General Location Error -->
                        <x-input-error :messages="$errors->get('location')" class="mt-2 mb-4" />
                        <x-input-error :messages="$errors->get('photo')" class="mt-2 mb-4" />

                        <!-- Camera Viewfinder -->
                        <div class="mb-4">
                            <x-input-label for="camera" :value="__('Ambil Foto Absensi')" />
                            <div class="mt-2 relative">
                                <video id="camera-viewfinder" autoplay playsinline class="w-full h-auto bg-gray-200 rounded-md"></video>
                                <canvas id="camera-canvas" class="hidden"></canvas>
                                <img id="photo-preview" src="" alt="Pratinjau Foto" class="w-full h-auto rounded-md hidden" />
                            </div>
                            <div class="mt-4 flex justify-center space-x-4">
                                <button type="button" id="capture-button" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Ambil Gambar</button>
                                <button type="button" id="recapture-button" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-400 hidden">Ambil Ulang</button>
                            </div>
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
                            <x-primary-button id="submit-attendance-button" class="ml-4">
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
            // DOM Elements
            const attendanceMessageDiv = document.getElementById('attendance-message');
            const submitButton = document.getElementById('submit-attendance-button');
            const form = document.getElementById('attendance-form');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const mapDiv = document.getElementById('map');
            
            // Camera Elements
            const video = document.getElementById('camera-viewfinder');
            const canvas = document.getElementById('camera-canvas');
            const photoPreview = document.getElementById('photo-preview');
            const captureButton = document.getElementById('capture-button');
            const recaptureButton = document.getElementById('recapture-button');
            let capturedImageBlob = null;

            // Data from Controller
            const todayAttendance = @json($todayAttendance);

            // --- 1. Initialize UI based on Attendance Status ---
            function initializeUI() {
                let message = '';
                let buttonText = '';
                let isFormDisabled = false;

                if (!todayAttendance) {
                    message = '<p class="font-bold">Anda akan melakukan Absen Masuk.</p><p class="text-sm">Pastikan Anda berada di lokasi yang benar dan siap mengambil foto.</p>';
                    buttonText = 'Absen Masuk';
                } else if (!todayAttendance.time_out) {
                    message = `<p class="font-bold">Anda akan melakukan Absen Pulang.</p><p class="text-sm">Anda sudah absen masuk pada: ${new Date(todayAttendance.time_in).toLocaleString('id-ID')}</p>`;
                    buttonText = 'Absen Pulang';
                } else {
                    message = `<p class="font-bold">Anda sudah melakukan Absen Masuk dan Pulang hari ini.</p><p class="text-sm">Masuk: ${new Date(todayAttendance.time_in).toLocaleString('id-ID')}</p><p class="text-sm">Pulang: ${new Date(todayAttendance.time_out).toLocaleString('id-ID')}</p><p class="text-sm">Tipe Absensi: ${todayAttendance.type || 'N/A'}</p><p class="text-sm mt-2">Anda tidak dapat melakukan absensi lagi hari ini.</p>`;
                    buttonText = 'Absensi Selesai';
                    isFormDisabled = true;
                }

                attendanceMessageDiv.innerHTML = message;
                submitButton.textContent = buttonText;
                if (isFormDisabled) {
                    submitButton.setAttribute('disabled', 'true');
                    captureButton.setAttribute('disabled', 'true');
                } else {
                    startCamera();
                }
            }

            // --- 2. Camera Logic ---
            async function startCamera() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    video.srcObject = stream;
                    video.classList.remove('hidden');
                    photoPreview.classList.add('hidden');
                    recaptureButton.classList.add('hidden');
                    captureButton.classList.remove('hidden');
                    capturedImageBlob = null;
                } catch (err) {
                    console.error("Error accessing camera: ", err);
                    attendanceMessageDiv.innerHTML = '<p class="font-bold text-red-700">Error: Tidak dapat mengakses kamera.</p><p class="text-sm text-red-600">Pastikan Anda memberikan izin akses kamera di browser Anda dan menggunakan koneksi HTTPS.</p>';
                    captureButton.setAttribute('disabled', 'true');
                }
            }

            captureButton.addEventListener('click', () => {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(blob => {
                    capturedImageBlob = blob;
                    photoPreview.src = URL.createObjectURL(blob);
                    
                    // UI update
                    video.classList.add('hidden');
                    photoPreview.classList.remove('hidden');
                    captureButton.classList.add('hidden');
                    recaptureButton.classList.remove('hidden');

                    // Stop the camera stream
                    video.srcObject.getTracks().forEach(track => track.stop());
                }, 'image/jpeg', 0.9);
            });

            recaptureButton.addEventListener('click', startCamera);


            // --- 3. Geolocation Logic ---
            function initializeGeolocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        latitudeInput.value = lat;
                        longitudeInput.value = lon;

                        const map = L.map(mapDiv).setView([lat, lon], 16);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        L.marker([lat, lon]).addTo(map).bindPopup('Lokasi Anda').openPopup();
                    }, () => {
                        alert('Gagal mendapatkan lokasi. Pastikan izin lokasi telah diberikan.');
                    });
                } else {
                    alert("Browser Anda tidak mendukung geolokasi.");
                }
            }

            // --- 4. Form Submission Logic ---
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                
                if (!capturedImageBlob) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Silakan ambil foto terlebih dahulu.' });
                    return;
                }

                submitButton.setAttribute('disabled', 'true');
                submitButton.textContent = 'Mengirim...';

                const formData = new FormData(form);
                formData.append('photo', capturedImageBlob, 'attendance.jpg');

                try {
                    const response = await fetch("{{ route('attendances.store') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json' // Expect a JSON response
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        // Handle validation errors or other server errors
                        let errorText = result.message || 'Terjadi kesalahan.';
                        if (result.errors) {
                            errorText = Object.values(result.errors).flat().join('\n');
                        }
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: errorText });
                        submitButton.removeAttribute('disabled');
                        submitButton.textContent = todayAttendance && !todayAttendance.time_out ? 'Absen Pulang' : 'Absen Masuk';
                    } else {
                        // Success
                        window.location.href = result.redirect_url;
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Tidak dapat terhubung ke server.' });
                    submitButton.removeAttribute('disabled');
                }
            });

            // --- Initializations ---
            initializeUI();
            initializeGeolocation();
        });
    </script>
    @endpush
</x-app-layout>