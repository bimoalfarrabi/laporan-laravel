@props(['imageUrl' => ''])

<div
    x-data="{ show: false, imageUrl: '{{ $imageUrl }}' }"
    x-show="show"
    x-on:open-modal.window="show = true; imageUrl = $event.detail.imageUrl"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
>
    <div @click.away="show = false" class="relative p-4">
        <button @click="show = false" class="absolute -top-2 -right-2 text-white text-3xl">&times;</button>
        <img :src="imageUrl" alt="Image" class="max-w-screen-lg max-h-screen-lg">
    </div>
</div>
