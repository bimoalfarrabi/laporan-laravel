<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Absensi Baru') }}
            </h2>
            @can('viewAny', App\Models\Attendance::class)
                <a href="{{ route('attendances.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:text-gray-500 dark:hover:text-gray-100 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:bg-gray-50 dark:active:bg-gray-600 active:text-gray-800 dark:active:text-gray-200 transition ease-in-out duration-150">
                    {{ __('Lihat Daftar Absensi') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-4 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div id="attendance-message"
                        class="mb-4 p-4 bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-700 text-blue-700 dark:text-blue-200 rounded-lg">
                        <span id="dynamic-attendance-status">
                            <!-- Pesan status absensi akan dimuat di sini -->
                        </span>
                        <div
                            class="mt-2 p-2 bg-yellow-100 dark:bg-yellow-900 border-l-4 border-yellow-500 dark:border-yellow-700 text-yellow-700 dark:text-yellow-200 rounded-lg">
                            <p class="font-bold">Peringatan:</p>
                            <p class="text-sm">Pastikan wajah anda nampak jelas didalam frame wajah, jangan pakai masker
                                / penutup wajah lainya</p>
                        </div>
                    </div>

                    <div
                        class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm text-sm text-gray-700 dark:text-gray-300">
                        <p><strong>Waktu Saat Ini:</strong> <span id="current-time">Memuat...</span></p>
                        <p><strong>Latitude:</strong> <span id="display-latitude">Memuat...</span></p>
                        <p><strong>Longitude:</strong> <span id="display-longitude">Memuat...</span></p>
                    </div>

                    <form id="attendance-form" action="{{ route('attendances.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <!-- General Location/Photo Error -->
                        <x-input-error :messages="$errors->get('location')" class="mt-2 mb-4" />
                        <x-input-error :messages="$errors->get('photo')" class="mt-2 mb-4" />

                        <div class="form-container">
                            <!-- Camera Viewfinder -->
                            <div class="form-section">
                                <x-input-label for="camera" :value="__('Ambil Foto Absensi')" class="text-center sm:text-left" />
                                <div id="camera" class="mt-2">
                                    <video id="camera-viewfinder" autoplay playsinline></video>
                                </div>
                                <canvas id="camera-canvas" class="hidden"></canvas>
                            </div>
                        </div>

                        <div class="flex items-center justify-center mt-4">
                            <x-primary-button id="submit-attendance-button" class="flex items-center">
                                <span id="button-text">{{ __('Kirim Absensi') }}</span>
                                <span id="loading-spinner" class="hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </span>
                            </x-primary-button>
                        </div>

                        <div class="form-container">
                            <!-- Location -->
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">

                            <!-- Map -->
                            <div class="form-section">
                                <x-input-label for="map" :value="__('Lokasi Anda')" class="text-center sm:text-left" />
                                <div id="map-container" class="relative mt-2 rounded-md border-gray-300"
                                    style="min-height: 200px;">
                                    <div id="map-loading-indicator"
                                        class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 bg-opacity-75 z-10">
                                        <svg class="animate-spin h-8 w-8 text-gray-500 dark:text-gray-400"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Memuat peta...</span>
                                    </div>
                                    <div id="map" class="w-full h-full"></div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
            <style>
                /* Default Mobile Styles */
                .form-container {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }

                #camera {
                    width: 260px;
                    /* Consistent size for all screens */
                    height: 260px;
                    /* Consistent size for all screens */
                    border-radius: 50%;
                    overflow: hidden;
                    display: block;
                    margin: 0 auto;
                    border: 5px solid #000000;
                    position: relative;
                }

                #map {
                    height: 200px;
                    /* Smaller map on mobile */
                    width: 100%;
                }

                /* Desktop Styles */
                @media (min-width: 640px) {
                    .form-container {
                        flex-direction: row;
                        justify-content: space-between;
                        align-items: flex-start;
                    }

                    .form-section {
                        width: 48%;
                    }

                    /* #camera styles are now consistent, no need to override here */
                    #map {
                        height: 300px;
                        /* Larger map on desktop */
                    }
                }

                #camera video {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    position: absolute;
                    top: 0;
                    left: 0;
                    transform: scaleX(-1);
                    /* Mirror effect for selfie camera */
                }

                #camera::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 150px;
                    height: 190px;
                    border-radius: 45% 45% 80% 80%;
                    border: 3px solid rgb(58, 58, 58);
                    transform: translate(-50%, -50%);
                    z-index: 1;
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                // The existing JavaScript logic remains the same
                document.addEventListener('DOMContentLoaded', function() {
                    const attendanceMessageDiv = document.getElementById('attendance-message');
                    const dynamicAttendanceStatusSpan = document.getElementById('dynamic-attendance-status');
                    const submitButton = document.getElementById('submit-attendance-button');
                    const buttonTextSpan = document.getElementById('button-text'); // New
                    const loadingSpinnerSpan = document.getElementById('loading-spinner'); // New
                    const form = document.getElementById('attendance-form');
                    const latitudeInput = document.getElementById('latitude');
                    const longitudeInput = document.getElementById('longitude');
                    const mapDiv = document.getElementById('map');
                    const video = document.getElementById('camera-viewfinder');
                    const canvas = document.getElementById('camera-canvas');
                    const mapLoadingIndicator = document.getElementById('map-loading-indicator');
                    const currentTimeSpan = document.getElementById('current-time'); // New
                    const displayLatitudeSpan = document.getElementById('display-latitude'); // New
                    const displayLongitudeSpan = document.getElementById('display-longitude'); // New
                    const todayAttendance = @json($todayAttendance);

                    let originalButtonText = buttonTextSpan.textContent; // Store original text

                    // Function to update current time
                    function updateCurrentTime() {
                        const now = new Date();
                        currentTimeSpan.textContent = now.toLocaleString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }

                    // Update time every second
                    setInterval(updateCurrentTime, 1000);
                    updateCurrentTime(); // Initial call

                    function initializeUI() {
                        let message = '';
                        let buttonCurrentText = '';
                        let isFormDisabled = false;

                        if (!todayAttendance) {
                            message =
                                '<p class="font-bold">Anda akan melakukan Absen Masuk.</p><p class="text-sm">Posisikan wajah Anda di dalam bingkai dan klik tombol Absen Masuk.</p>';
                            buttonCurrentText = 'Absen Masuk';
                        } else if (!todayAttendance.time_out) {
                            message =
                                `<p class="font-bold">Anda akan melakukan Absen Pulang.</p><p class="text-sm">Anda sudah absen masuk pada: ${new Date(todayAttendance.time_in).toLocaleString('id-ID')}</p>`;
                            buttonCurrentText = 'Absen Pulang';
                        } else {
                            message =
                                `<p class="font-bold">Anda sudah melakukan Absen Masuk dan Pulang hari ini.</p><p class="text-sm">Masuk: ${new Date(todayAttendance.time_in).toLocaleString('id-ID')}</p><p class="text-sm">Pulang: ${new Date(todayAttendance.time_out).toLocaleString('id-ID')}</p><p class="text-sm">Tipe Absensi: ${todayAttendance.type || 'N/A'}</p><p class="text-sm mt-2">Anda tidak dapat melakukan absensi lagi hari ini.</p>`;
                            buttonCurrentText = 'Absensi Selesai';
                            isFormDisabled = true;
                        }

                        dynamicAttendanceStatusSpan.innerHTML = message;
                        buttonTextSpan.textContent = buttonCurrentText;
                        originalButtonText = buttonCurrentText; // Update original text
                        if (isFormDisabled) {
                            submitButton.setAttribute('disabled', 'true');
                            loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                            document.getElementById('camera').style.display = 'none';
                        } else {
                            startCamera();
                        }
                    }

                    async function startCamera() {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: 'user'
                                }
                            });
                            video.srcObject = stream;
                        } catch (err) {
                            console.error("Error accessing camera: ", err);
                            dynamicAttendanceStatusSpan.innerHTML =
                                '<p class="font-bold text-red-700">Error: Tidak dapat mengakses kamera.</p><p class="text-sm text-red-600">Pastikan Anda memberikan izin akses kamera di browser Anda dan menggunakan koneksi HTTPS.</p>';
                            submitButton.setAttribute('disabled', 'true');
                            loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                        }
                    }

                    function initializeGeolocation() {
                        mapLoadingIndicator.classList.remove('hidden'); // Show loading indicator
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(position => {
                                const lat = position.coords.latitude;
                                const lon = position.coords.longitude;
                                const accuracy = position.coords.accuracy;
                                const timestamp = position.timestamp;

                                // Mock Location Detection Heuristics
                                const currentTime = Date.now();
                                const locationAge = (currentTime - timestamp) / 1000; // in seconds

                                // Heuristic 1: Suspiciously low accuracy (e.g., 0 or very close to 0)
                                // Real GPS rarely has perfect accuracy. A threshold of < 5m is often a red flag.
                                if (accuracy < 5) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Deteksi Lokasi Palsu!',
                                        text: 'Akurasi lokasi Anda terlalu tinggi (' + accuracy.toFixed(2) +
                                            'm). Ini mungkin indikasi penggunaan lokasi palsu. Absensi dibatalkan.',
                                        allowOutsideClick: false,
                                        showConfirmButton: true,
                                    }).then(() => {
                                        window.location.href = "{{ route('attendances.index') }}";
                                    });
                                    submitButton.setAttribute('disabled', 'true');
                                    loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                                    mapLoadingIndicator.classList.add('hidden');
                                    displayLatitudeSpan.textContent = 'Deteksi Palsu';
                                    displayLongitudeSpan.textContent = 'Deteksi Palsu';
                                    return;
                                }

                                // Heuristic 2: Stale location data (older than, e.g., 60 seconds)
                                if (locationAge > 60) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lokasi Kedaluwarsa!',
                                        text: 'Data lokasi Anda terlalu lama (' + locationAge.toFixed(0) +
                                            ' detik yang lalu). Harap coba lagi untuk mendapatkan lokasi terbaru. Absensi dibatalkan.',
                                        allowOutsideClick: false,
                                        showConfirmButton: true,
                                    }).then(() => {
                                        window.location.href = "{{ route('attendances.index') }}";
                                    });
                                    submitButton.setAttribute('disabled', 'true');
                                    loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                                    mapLoadingIndicator.classList.add('hidden');
                                    displayLatitudeSpan.textContent = 'Kedaluwarsa';
                                    displayLongitudeSpan.textContent = 'Kedaluwarsa';
                                    return;
                                }

                                latitudeInput.value = lat;
                                longitudeInput.value = lon;
                                displayLatitudeSpan.textContent = lat.toFixed(6); // Display with 6 decimal places
                                displayLongitudeSpan.textContent = lon.toFixed(6); // Display with 6 decimal places

                                const map = L.map(mapDiv).setView([lat, lon], 16);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);
                                L.marker([lat, lon]).addTo(map).bindPopup('Lokasi Anda').openPopup();
                                mapLoadingIndicator.classList.add('hidden'); // Hide loading indicator on success
                            }, (error) => {
                                let errorMessage =
                                    'Gagal mendapatkan lokasi. Pastikan izin lokasi telah diberikan.';
                                if (error.code === error.PERMISSION_DENIED) {
                                    errorMessage = 'Izin lokasi ditolak. Harap izinkan akses lokasi untuk absensi.';
                                } else if (error.code === error.POSITION_UNAVAILABLE) {
                                    errorMessage = 'Informasi lokasi tidak tersedia.';
                                } else if (error.code === error.TIMEOUT) {
                                    errorMessage = 'Waktu tunggu untuk mendapatkan lokasi habis. Harap coba lagi.';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error Lokasi!',
                                    text: errorMessage + ' Absensi dibatalkan.',
                                    allowOutsideClick: false,
                                    showConfirmButton: true,
                                }).then(() => {
                                    window.location.href = "{{ route('attendances.index') }}";
                                });
                                submitButton.setAttribute('disabled', 'true');
                                loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                                mapLoadingIndicator.classList.add('hidden'); // Hide loading indicator on error
                                displayLatitudeSpan.textContent = 'Error';
                                displayLongitudeSpan.textContent = 'Error';
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000, // 10 seconds timeout
                                maximumAge: 0 // Force current location
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Browser Tidak Mendukung!',
                                text: 'Browser Anda tidak mendukung geolokasi. Absensi dibatalkan.',
                                allowOutsideClick: false,
                                showConfirmButton: true,
                            }).then(() => {
                                window.location.href = "{{ route('attendances.index') }}";
                            });
                            submitButton.setAttribute('disabled', 'true');
                            loadingSpinnerSpan.classList.add('hidden'); // Ensure spinner is hidden
                            mapLoadingIndicator.classList.add('hidden'); // Hide loading indicator if not supported
                            displayLatitudeSpan.textContent = 'Tidak tersedia';
                            displayLongitudeSpan.textContent = 'Tidak tersedia';
                        }
                    }

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        submitButton.setAttribute('disabled', 'true');
                        buttonTextSpan.textContent = 'Memproses...'; // Update text
                        loadingSpinnerSpan.classList.remove('hidden'); // Show spinner

                        const imageBlob = await new Promise(resolve => {
                            const context = canvas.getContext('2d');
                            const size = 260; // Match the CSS size
                            canvas.width = size;
                            canvas.height = size;

                            const videoRatio = video.videoWidth / video.videoHeight;
                            let sourceWidth, sourceHeight, sx, sy;

                            if (videoRatio > 1) {
                                sourceHeight = video.videoHeight;
                                sourceWidth = sourceHeight;
                                sx = (video.videoWidth - sourceWidth) / 2;
                                sy = 0;
                            } else {
                                sourceWidth = video.videoWidth;
                                sourceHeight = sourceWidth;
                                sx = 0;
                                sy = (video.videoHeight - sourceHeight) / 2;
                            }

                            context.drawImage(video, sx, sy, sourceWidth, sourceHeight, 0, 0, size,
                                size);
                            canvas.toBlob(resolve, 'image/jpeg', 0.9);
                        });

                        if (!imageBlob) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Gagal mengambil gambar. Silakan coba lagi.'
                            }).then(() => {
                                window.location.href = "{{ route('attendances.index') }}";
                            });
                            submitButton.removeAttribute('disabled');
                            buttonTextSpan.textContent = originalButtonText; // Revert text
                            loadingSpinnerSpan.classList.add('hidden'); // Hide spinner
                            return;
                        }

                        if (video.srcObject) {
                            video.srcObject.getTracks().forEach(track => track.stop());
                        }

                        const formData = new FormData(form);
                        formData.append('photo', imageBlob, 'attendance.jpg');

                        try {
                            const response = await fetch("{{ route('attendances.store') }}", {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                let errorText = result.message || 'Terjadi kesalahan.';
                                if (result.errors) {
                                    errorText = Object.values(result.errors).flat().join('\n');
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: errorText
                                }).then(() => {
                                    window.location.href = "{{ route('attendances.index') }}";
                                });
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: result.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(() => {
                                    window.location.href = result.redirect_url;
                                });
                            }
                        } catch (error) {
                            console.error('Submission error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Tidak dapat terhubung ke server.'
                            }).then(() => {
                                window.location.href = "{{ route('attendances.index') }}";
                            });
                        } finally {
                            submitButton.removeAttribute('disabled');
                            buttonTextSpan.textContent = originalButtonText; // Revert text
                            loadingSpinnerSpan.classList.add('hidden'); // Hide spinner
                        }
                    });

                    initializeUI();
                    initializeGeolocation();
                });
            </script>
        @endpush
</x-app-layout>
