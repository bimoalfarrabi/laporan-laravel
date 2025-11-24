<div x-data="{
    show: false,
    videoUrl: '',
    videoFileName: '',
    isLoading: true
}" x-show="show"
    x-on:open-video-modal.window="show = true; videoUrl = $event.detail.videoUrl; videoFileName = $event.detail.videoFileName; isLoading = true"
    x-on:keydown.escape.window="show = false" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <!-- Background Overlay -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black bg-opacity-90" @click="show = false"></div>

    <!-- Modal Content -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="relative z-10 flex flex-col items-center w-full max-w-5xl">

        <!-- Video Player Container -->
        <div class="relative w-full rounded-xl overflow-hidden bg-gray-900 shadow-2xl">
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

            <video :src="videoUrl" controls autoplay preload="metadata"
                class="w-full h-auto transition-opacity duration-300"
                style="max-height: 85vh;"
                @canplaythrough="isLoading = false"
                :class="{ 'opacity-0': isLoading }"></video>
        </div>

        <!-- Video Filename -->
        <div class="mt-4 text-white text-center">
            <p class="text-sm font-medium" x-text="videoFileName"></p>
        </div>

        <!-- Close Button -->
        <button @click="show = false"
            class="absolute -top-12 right-0 text-white bg-gray-800/90 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Download Button -->
        <a :href="videoUrl" download
            class="absolute -top-12 right-12 text-white bg-gray-800/90 rounded-full p-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition shadow-lg"
            title="Download video">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        </a>
    </div>
</div>
