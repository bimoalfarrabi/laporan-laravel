<div
    x-data="{
        show: false,
        imageUrl: '',
        rotation: 0,
        isLoading: true,
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
        }
    }"
    x-show="show"
    x-on:open-modal.window="show = true; imageUrl = $event.detail.imageUrl; setRotationFromExif($event.detail.imageUrl)"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-cloak
>
    <!-- Background Overlay -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black bg-opacity-75"
        @click="show = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="relative z-10 flex flex-col items-center"
    >
        <div class="relative">
            <!-- Loading Spinner -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-800/50 rounded-lg">
                <svg class="animate-spin h-10 w-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <img :src="imageUrl" @load="isLoading = false" alt="Image" class="max-w-[90vw] max-h-[80vh] object-contain rounded-lg shadow-lg" :style="{ transform: `rotate(${rotation}deg)` }" x-show="!isLoading">

            <button @click="rotation -= 90" class="absolute top-1/2 -translate-y-1/2 -left-16 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 19.5L3 15m0 0l4.5-4.5M3 15h13.5a6 6 0 000-12H3" />
                </svg>
            </button>

            <button @click="rotation += 90" class="absolute top-1/2 -translate-y-1/2 -right-16 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 19.5L21 15m0 0l-4.5-4.5M21 15H7.5a6 6 0 010-12H21" />
                </svg>
            </button>
        </div>

        <button @click="show = false" class="absolute -top-10 -right-2 sm:top-2 sm:right-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>