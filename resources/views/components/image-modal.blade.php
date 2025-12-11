<div x-data="{
    show: false,
    imageUrl: '',
    fullImageUrl: '',
    isLoading: true,
    reportId: null,
    imagePath: null,
    hasPrev: false,
    hasNext: false,
    handleImageLoad(event) {
        const img = event.target;
        if (typeof img.decode === 'function') {
            img.decode().then(() => {
                this.isLoading = false;
            }).catch(e => {
                console.error('Image decoding error:', e);
                this.isLoading = false; // Fallback on error
            });
        } else {
            setTimeout(() => {
                this.isLoading = false;
            }, 0);
        }
    },
    navigate(direction) {
        $dispatch('navigate-gallery', direction);
    }
}" x-show="show"
    x-on:open-modal.window="
        show = true; 
        imageUrl = $event.detail.imageUrl; 
        fullImageUrl = $event.detail.fullImageUrl; 
        reportId = $event.detail.reportId; 
        imagePath = $event.detail.imagePath; 
        hasPrev = $event.detail.hasPrev;
        hasNext = $event.detail.hasNext;
        isLoading = true;
        $nextTick(() => {
            const img = $el.querySelector('img');
            if (img && img.complete && img.naturalWidth > 0) {
                isLoading = false;
            }
        });
    "
    x-on:close-all-modals.window="show = false" x-on:keydown.escape.window="show = false"
    x-on:keydown.arrow-left.window="if(show && hasPrev) navigate(-1)"
    x-on:keydown.arrow-right.window="if(show && hasNext) navigate(1)" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <!-- Background Overlay -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black bg-opacity-75" @click="show = false"></div>

    <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95" class="relative z-10 w-full pointer-events-none">

        <div class="relative mx-auto max-w-[90vw] max-h-[90vh] flex items-center justify-center pointer-events-auto">
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

            <img :src="imageUrl" @load="handleImageLoad($event)" alt="Image"
                class="object-contain rounded-lg shadow-lg max-w-full max-h-[85vh] w-auto h-auto mx-auto"
                x-show="!isLoading">

            <!-- Action Buttons Container -->
            <div x-show="!isLoading" class="absolute inset-0 w-full h-full pointer-events-none">

                <!-- Prev Button -->
                <button x-show="hasPrev" @click="navigate(-1)"
                    class="absolute left-0 top-1/2 -translate-y-1/2 -ml-4 md:-ml-16 text-white bg-black/50 hover:bg-black/70 rounded-full p-3 focus:outline-none transition pointer-events-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Next Button -->
                <button x-show="hasNext" @click="navigate(1)"
                    class="absolute right-0 top-1/2 -translate-y-1/2 -mr-4 md:-mr-16 text-white bg-black/50 hover:bg-black/70 rounded-full p-3 focus:outline-none transition pointer-events-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <a :href="fullImageUrl" target="_blank"
                    class="absolute top-4 left-4 text-white bg-gray-800/75 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition pointer-events-auto"
                    title="Buka gambar ukuran penuh">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>

                <!-- Close Button -->
                <button @click="show = false"
                    class="absolute top-4 right-4 text-white bg-gray-800/75 rounded-full p-1 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition pointer-events-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
