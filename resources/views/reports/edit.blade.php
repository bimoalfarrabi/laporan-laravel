<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Laporan Dinamis: ') . $report->reportType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('reports.update', $report->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="report_type_id" value="{{ $report->reportType->id }}">

                        @foreach ($report->reportType->reportTypeFields as $field)
                            <div class="mt-4">
                                <x-input-label for="{{ $field->name }}" :value="__($field->label)" />

                                @if (
                                    $field->type === 'text' ||
                                        $field->type === 'date' ||
                                        $field->type === 'time' ||
                                        $field->type === 'number' ||
                                        $field->type === 'role_specific_text')
                                    <input id="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="{{ $field->type === 'role_specific_text' ? 'text' : $field->type }}"
                                        name="{{ $field->name }}"
                                        value="{{ old($field->name, $report->data[$field->name] ?? '') }}"
                                        {{ $field->required ? 'required' : '' }} />
                                @elseif ($field->type === 'textarea')
                                    <textarea id="{{ $field->name }}" name="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ $field->required ? 'required' : '' }}>{{ old($field->name, $report->data[$field->name] ?? '') }}</textarea>
                                @elseif ($field->type === 'select')
                                    {{-- Assuming 'select' type will still have options --}}
                                    {{-- This part needs significant re-evaluation: where do options come from now? --}}
                                    {{-- For now, commenting out or simplifying --}}
                                    {{-- You would likely need to store options in ReportTypeField or a related model --}}
                                    <select id="{{ $field->name }}" name="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ $field->required ? 'required' : '' }}>
                                        <option value="">Pilih {{ $field->label }}</option>

                                    </select>
                                @elseif ($field->type === 'checkbox')
                                    <input type="checkbox" id="{{ $field->name }}" name="{{ $field->name }}"
                                        value="1"
                                        {{ old($field->name, $report->data[$field->name] ?? false) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                @elseif ($field->type === 'file')
                                    <div x-data="fileEditHandler('{{ $field->name }}', {{ json_encode($report->data[$field->name] ?? []) }})" class="space-y-2">

                                        {{-- Existing Images --}}
                                        <div class="grid grid-cols-3 gap-4 mb-4" x-show="existingImages.length > 0">
                                            <template x-for="(path, index) in existingImages" :key="index">
                                                <div class="relative group" x-show="!deletedImages.includes(path)">
                                                    <img :src="'/storage/' + path"
                                                        class="w-full h-24 object-cover rounded-md border border-gray-300">
                                                    <button type="button" @click="markForDeletion(path)"
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                    {{-- Hidden input for deletion --}}
                                                    <input type="hidden" :name="'delete_{{ $field->name }}[]'"
                                                        :value="path" x-if="deletedImages.includes(path)">
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Hidden inputs for deleted images (rendered outside loop to ensure submission) --}}
                                        <template x-for="path in deletedImages" :key="path">
                                            <input type="hidden" name="delete_{{ $field->name }}[]"
                                                :value="path">
                                        </template>

                                        {{-- New File Input --}}
                                        <input id="{{ $field->name }}" type="file" multiple accept="image/*"
                                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            @change="handleFileSelect" />

                                        {{-- Hidden input to store actual new files --}}
                                        <input type="file" name="{{ $field->name }}[]"
                                            id="{{ $field->name }}_actual" multiple class="hidden">

                                        {{-- New Images Preview --}}
                                        <div class="grid grid-cols-3 gap-4 mt-2" id="{{ $field->name }}_new_preview">
                                            <template x-for="(image, index) in newImages" :key="index">
                                                <div class="relative group">
                                                    <img :src="image.url"
                                                        class="w-full h-24 object-cover rounded-md border border-gray-300">
                                                    <button type="button" @click="removeNewImage(index)"
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <p class="text-sm text-gray-500">Maksimal 3 gambar total (termasuk yang sudah
                                            ada).</p>
                                    </div>
                                @elseif ($field->type === 'video')
                                    <div x-data="videoEditHandler('{{ $field->name }}', '{{ $report->data[$field->name] ?? '' }}')" class="space-y-3">
                                        {{-- Existing Video --}}
                                        <div x-show="existingVideoUrl && !videoFile && !isMarkedForDeletion"
                                            class="mt-2">
                                            <div
                                                class="relative rounded-xl overflow-hidden bg-gray-900 shadow-lg border border-gray-300 max-w-3xl group">
                                                <video :src="'/storage/' + existingVideoUrl" controls preload="metadata"
                                                    class="w-full h-auto" style="max-height: 500px;">
                                                    Browser Anda tidak mendukung video player.
                                                </video>
                                                <button type="button" @click="markForDeletion"
                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 focus:outline-none shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-2"
                                                x-text="existingVideoUrl.split('/').pop()"></p>
                                        </div>

                                        {{-- Hidden input for deletion --}}
                                        <template x-if="isMarkedForDeletion">
                                            <input type="hidden" name="delete_{{ $field->name }}"
                                                :value="existingVideoUrl">
                                        </template>

                                        {{-- New Video Input --}}
                                        <div x-show="!existingVideoUrl || isMarkedForDeletion">
                                            <input id="{{ $field->name }}" type="file" accept="video/*"
                                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                @change="handleFileSelect" name="{{ $field->name }}">
                                        </div>

                                        {{-- New Video Preview --}}
                                        <div class="mt-3" x-show="videoFile" style="display: none;">
                                            <div
                                                class="relative rounded-xl overflow-hidden bg-gray-900 shadow-lg border border-gray-300 max-w-3xl">
                                                <video :src="videoPreviewUrl" controls preload="metadata"
                                                    class="w-full h-auto" style="max-height: 500px;">
                                                    Browser Anda tidak mendukung video player.
                                                </video>
                                            </div>
                                            <div class="flex items-center justify-between mt-2">
                                                <p class="text-sm text-gray-600" x-text="videoFileName"></p>
                                                <button type="button" @click="removeVideo"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    Hapus Video
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500">Maksimal 1 video.</p>
                                    </div>
                                @endif
                                <x-input-error :messages="$errors->get($field->name)" class="mt-2" />
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Laporan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fileEditHandler', (fieldName, initialData) => ({
                existingImages: [],
                deletedImages: [],
                newImages: [],
                newFiles: [],

                init() {
                    // Handle legacy string format or array format
                    if (typeof initialData === 'string') {
                        this.existingImages = [initialData];
                    } else if (Array.isArray(initialData)) {
                        this.existingImages = initialData;
                    }
                },

                markForDeletion(path) {
                    if (!this.deletedImages.includes(path)) {
                        this.deletedImages.push(path);
                    }
                },

                handleFileSelect(event) {
                    const incomingFiles = Array.from(event.target.files);

                    // Calculate current total: (existing - deleted) + (current new) + (incoming)
                    const currentActiveCount = this.existingImages.length - this.deletedImages.length;
                    const totalAfterAdd = currentActiveCount + this.newFiles.length + incomingFiles
                        .length;

                    if (totalAfterAdd > 3) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Maksimal total 3 gambar yang diperbolehkan.',
                        });
                        event.target.value = ''; // Reset input
                        return;
                    }

                    incomingFiles.forEach(file => {
                        if (!file.type.startsWith('image/')) return;

                        this.newFiles.push(file);
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.newImages.push({
                                url: e.target.result,
                                file: file
                            });
                        };
                        reader.readAsDataURL(file);
                    });

                    this.updateActualInput();
                    event.target.value = ''; // Reset input
                },

                removeNewImage(index) {
                    this.newImages.splice(index, 1);
                    this.newFiles.splice(index, 1);
                    this.updateActualInput();
                },

                updateActualInput() {
                    const dataTransfer = new DataTransfer();
                    this.newFiles.forEach(file => dataTransfer.items.add(file));
                    document.getElementById(fieldName + '_actual').files = dataTransfer.files;
                }
            }));

            Alpine.data('videoEditHandler', (fieldName, initialVideo) => ({
                existingVideoUrl: initialVideo,
                videoFile: null,
                videoPreviewUrl: null,
                videoFileName: '',
                isMarkedForDeletion: false,

                markForDeletion() {
                    this.isMarkedForDeletion = true;
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('video/')) {
                        this.videoFile = file;
                        this.videoPreviewUrl = URL.createObjectURL(file);
                        this.videoFileName = file.name;
                    }
                },

                removeVideo() {
                    // Revoke the object URL to free memory
                    if (this.videoPreviewUrl) {
                        URL.revokeObjectURL(this.videoPreviewUrl);
                    }
                    this.videoFile = null;
                    this.videoPreviewUrl = null;
                    this.videoFileName = '';
                    document.getElementById(fieldName).value = '';
                }
            }));
        });
    </script>
</x-app-layout>
