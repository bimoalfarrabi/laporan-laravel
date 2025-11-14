@props(['imageUrl' => ''])

<div
    x-data="{ show: false, imageUrl: '' }"
    x-show="show"
    x-on:open-modal.window="show = true; imageUrl = $event.detail.imageUrl"
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
        class="relative z-10"
    >
        <img :src="imageUrl" alt="Image" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-lg transform rotate-90">

        <button @click="show = false" class="absolute top-2 right-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

