<script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
<div x-data="{
    show: false,
    imageUrl: '',
    fullImageUrl: '',
    rotation: 0,
    isLoading: true,
    reportId: null,
    imagePath: null,
    async setRotationFromExif(url) {
        this.isLoading = true;
        this.rotation = 0; // Reset rotation
        try {
            // Fetch the image as a blob to read EXIF data
            const response = await fetch(url);
            const blob = await response.blob();
            const self = this;

            EXIF.getData(blob, function() {
                const orientation = EXIF.getTag(this, 'Orientation');
                let newRotation = 0;
                switch (orientation) {
                    case 3:
                        newRotation = 180;
                        break;
                    case 6:
                        newRotation = 90;
                        break;
                    case 8:
                        newRotation = 270;
                        break;
                    default:
                        newRotation = 0;
                }
                self.rotation = newRotation;
                self.isLoading = false;
            });
        } catch (e) {
            console.error('Could not get EXIF data:', e);
            this.isLoading = false; // Stop loading on error
            this.rotation = 0; // Default to 0 on error
        }
    },
    async saveRotation() {
        if (!this.reportId || !this.imagePath || this.rotation === 0) return;

        // Normalize rotation to -90, 90, 180
        // We only support these for now as per backend
        // If rotation is 270, it's -90.
        // If rotation is 360, it's 0.
        let angle = this.rotation % 360;
        if (angle === 270) angle = -90;
        if (angle === -270) angle = 90;

        if (angle === 0) return;

        // Backend expects 90, -90, 180.
        // If we have something else (e.g. 450), we normalize.
        // But UI only allows 90 increments.

        if (![90, -90, 180].includes(angle)) {
            // Try to normalize further if user spun it around multiple times
            if (Math.abs(angle) === 180) angle = 180;
            else if (angle === -90 || angle === 270) angle = -90;
            else if (angle === 90 || angle === -270) angle = 90;
            else {
                alert('Rotasi tidak valid. Gunakan kelipatan 90 derajat.');
                return;
            }
        }

        if (!confirm('Apakah Anda yakin ingin menyimpan rotasi ini secara permanen?')) return;

        this.isLoading = true;
        try {
            const response = await fetch(`/reports/${this.reportId}/rotate-image`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                },
                body: JSON.stringify({
                    image_path: this.imagePath,
                    angle: angle
                })
            });

            if (response.ok) {
                // Success
                this.rotation = 0;
                // Reload image by appending timestamp
                const timestamp = new Date().getTime();
                const separator = this.imageUrl.includes('?') ? '&' : '?';
                this.imageUrl = this.imageUrl + separator + 't=' + timestamp;
                this.fullImageUrl = this.fullImageUrl + (this.fullImageUrl.includes('?') ? '&' : '?') + 't=' + timestamp;

                // Dispatch event to update thumbnail in parent view
                window.dispatchEvent(new CustomEvent('image-rotated', {
                    detail: {
                        imagePath: this.imagePath,
                        timestamp: timestamp
                    }
                }));

                alert('Rotasi berhasil disimpan.');
            } else {
                const data = await response.json();
                alert('Gagal menyimpan rotasi: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (e) {
            console.error('Error saving rotation:', e);
            alert('Terjadi kesalahan jaringan.');
        } finally {
            this.isLoading = false;
        }
    }
}" x-show="show"
    x-on:open-modal.window="show = true; imageUrl = $event.detail.imageUrl; fullImageUrl = $event.detail.fullImageUrl; reportId = $event.detail.reportId; imagePath = $event.detail.imagePath; setRotationFromExif($event.detail.imageUrl); isLoading = true"
    x-on:keydown.escape.window="show = false" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <!-- Background Overlay -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black bg-opacity-75" @click="show = false"></div>

    <!-- Modal Content -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95" class="relative z-10 flex flex-col items-center">
        <div class="relative max-w-[90vw] max-h-[80vh] flex items-center justify-center">
            <!-- Loading Spinner -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-800/50 rounded-lg">
                <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>

            <img :src="imageUrl" @load="isLoading = false" alt="Image"
                class="object-contain rounded-lg shadow-lg max-w-full max-h-full"
                :style="{ transform: `rotate(${rotation}deg)` }" x-show="!isLoading">

            <!-- Rotation Buttons -->
            <button @click="rotation -= 90"
                class="absolute bottom-2 left-2 md:top-1/2 md:-translate-y-1/2 md:left-4 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.5 19.5L3 15m0 0l4.5-4.5M3 15h13.5a6 6 0 000-12H3" />
                </svg>
            </button>

            <button @click="rotation += 90"
                class="absolute bottom-2 right-2 md:top-1/2 md:-translate-y-1/2 md:right-4 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 19.5L21 15m0 0l-4.5-4.5M21 15H7.5a6 6 0 010-12H21" />
                </svg>
            </button>
            <a :href="fullImageUrl" target="_blank"
                class="absolute top-2 left-2 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition"
                title="Buka gambar ukuran penuh">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>

            <!-- Save Rotation Button -->
            <button x-show="rotation !== 0 && reportId && imagePath" @click="saveRotation()"
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-4 py-2 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <span>Simpan Rotasi</span>
            </button>
        </div>

        <!-- Close Button - positioned always at top-right of the modal wrapper -->
        <button @click="show = false"
            class="absolute top-2 right-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
