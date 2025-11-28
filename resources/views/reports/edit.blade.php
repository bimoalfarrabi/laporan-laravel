<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Laporan Dinamis: ') . $report->reportType->name }}
        </h2>
        <style>
            trix-toolbar .trix-button-group--text-tools,
            /* Bold, Italic, Strike, Link */
            trix-toolbar .trix-button--icon-heading-1,
            /* Heading */
            trix-toolbar .trix-button--icon-quote,
            /* Quote */
            trix-toolbar .trix-button--icon-code,
            /* Code */
            trix-toolbar .trix-button-group--file-tools

            /* File Attachment */
                {
                display: none !important;
            }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <script>
                        // Define a global base URL for serving files to be used by Alpine components
                        const fileServeUrl = '/storage/files/';
                    </script>
                    <form id="report-form-edit" method="POST" action="{{ route('reports.update', $report->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="report_type_id" value="{{ $report->reportType->id }}">

                        @foreach ($report->reportType->reportTypeFields as $field)
                            <div class="mt-4">
                                <x-input-label for="{{ $field->name }}">
                                    {{ __($field->label) }}
                                    @if ($field->required)
                                        <span
                                            class="text-red-600 text-xs font-bold ml-1 bg-red-100 px-2 py-0.5 rounded-full uppercase tracking-wider">(Wajib)</span>
                                    @endif
                                </x-input-label>

                                @if (
                                    $field->type === 'text' ||
                                        $field->type === 'date' ||
                                        $field->type === 'time' ||
                                        $field->type === 'number' ||
                                        $field->type === 'role_specific_text')
                                    <input id="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        type="{{ $field->type === 'role_specific_text' ? 'text' : $field->type }}"
                                        name="{{ $field->name }}"
                                        value="{{ old($field->name, $report->data[$field->name] ?? '') }}"
                                        {{ $field->required ? 'required' : '' }} />
                                @elseif ($field->type === 'textarea')
                                    <input id="{{ $field->name }}_input" type="hidden" name="{{ $field->name }}"
                                        value="{{ old($field->name, $report->data[$field->name] ?? '') }}">
                                    <trix-editor id="{{ $field->name }}" input="{{ $field->name }}_input"
                                        class="trix-content block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm min-h-[150px]"></trix-editor>
                                @elseif ($field->type === 'select')
                                    {{-- Assuming 'select' type will still have options --}}
                                    {{-- This part needs significant re-evaluation: where do options come from now? --}}
                                    {{-- For now, commenting out or simplifying --}}
                                    {{-- You would likely need to store options in ReportTypeField or a related model --}}
                                    <select id="{{ $field->name }}" name="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm transition duration-200 ease-in-out"
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
                                                    <img :src="fileServeUrl + path"
                                                        class="w-full h-24 object-cover rounded-md border border-gray-300">
                                                    <button type="button" @click="markForDeletion(path)"
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

                                        {{-- Hidden inputs for deleted images (rendered outside loop to ensure submission) --}}
                                        <template x-for="path in deletedImages" :key="path">
                                            <input type="hidden" name="delete_{{ $field->name }}[]"
                                                :value="path">
                                        </template>

                                        {{-- New File Input --}}
                                        <!-- Hidden file input -->
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Existing File Select -->
                                            <input id="{{ $field->name }}" type="file" multiple accept="image/*"
                                                class="hidden" @change="handleFileSelect" />

                                            <label for="{{ $field->name }}"
                                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-indigo-300 dark:hover:border-indigo-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:focus-within:ring-offset-gray-800 transition ease-in-out duration-150 cursor-pointer group">
                                                <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span
                                                    class="group-hover:text-indigo-600 transition-colors duration-150">Pilih
                                                    Foto</span>
                                            </label>

                                            <!-- Camera Capture -->
                                            <input id="{{ $field->name }}_camera" type="file" accept="image/*"
                                                capture="environment" class="hidden" @change="handleFileSelect" />

                                            <label for="{{ $field->name }}_camera"
                                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-indigo-300 dark:hover:border-indigo-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:focus-within:ring-offset-gray-800 transition ease-in-out duration-150 cursor-pointer group">
                                                <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span
                                                    class="group-hover:text-indigo-600 transition-colors duration-150">Ambil
                                                    Foto</span>
                                            </label>
                                        </div>

                                        {{-- Hidden input to store actual new files --}}
                                        <input type="file" name="{{ $field->name }}[]"
                                            id="{{ $field->name }}_actual" multiple class="hidden">

                                        {{-- New Images Preview --}}
                                        <div class="grid grid-cols-3 gap-4 mt-2" id="{{ $field->name }}_preview">
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
                                        <p class="text-sm text-gray-500">Maksimal 3 gambar.</p>
                                    </div>
                                @elseif ($field->type === 'video')
                                    <div x-data="videoEditHandler('{{ $field->name }}', {{ json_encode($report->data[$field->name] ?? null) }})" class="space-y-3">
                                        <!-- Hidden file input -->
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Existing File Select -->
                                            <input id="{{ $field->name }}" type="file" accept="video/*"
                                                class="hidden" @change="handleFileSelect" />

                                            <label for="{{ $field->name }}"
                                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-indigo-300 dark:hover:border-indigo-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:focus-within:ring-offset-gray-800 transition ease-in-out duration-150 cursor-pointer group">
                                                <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span
                                                    class="group-hover:text-indigo-600 transition-colors duration-150">Pilih
                                                    Video</span>
                                            </label>

                                            <!-- Camera Capture -->
                                            <input id="{{ $field->name }}_camera" type="file" accept="video/*"
                                                capture="environment" class="hidden" @change="handleFileSelect" />

                                            <label for="{{ $field->name }}_camera"
                                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-indigo-300 dark:hover:border-indigo-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:focus-within:ring-offset-gray-800 transition ease-in-out duration-150 cursor-pointer group">
                                                <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span
                                                    class="group-hover:text-indigo-600 transition-colors duration-150">Rekam
                                                    Video</span>
                                            </label>
                                        </div>
                                        <input type="file" name="{{ $field->name }}"
                                            id="{{ $field->name }}_actual" class="hidden">

                                        {{-- Compression Loading Overlay --}}
                                        <div x-show="isCompressing" style="display: none;"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md mx-4">
                                                <div class="flex items-start space-x-4">
                                                    <svg class="animate-spin h-8 w-8 text-indigo-600 flex-shrink-0"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    <div>
                                                        <h3
                                                            class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                            Mengompresi Video
                                                        </h3>
                                                        <p class="mt-1 text-sm text-gray-500"
                                                            x-text="compressionProgress">Memproses...</p>
                                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                                                                :style="'width: ' + compressionPercent + '%'"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Existing Video Preview -->
                                        <div x-show="existingVideoUrl && !isMarkedForDeletion && !videoPreviewUrl"
                                            class="relative group mb-4 max-w-full md:max-w-md">
                                            <div class="relative">
                                                <video :src="fileServeUrl + existingVideoUrl" controls
                                                    class="w-full h-auto rounded-md border border-gray-300 dark:border-gray-600"></video>
                                                <button type="button" @click="markForDeletion"
                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none shadow-md transition-transform transform hover:scale-110 z-10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Video Saat Ini
                                            </div>
                                        </div>

                                        {{-- Hidden input for deletion --}}
                                        <input type="hidden" name="delete_{{ $field->name }}"
                                            :value="isMarkedForDeletion ? existingVideoUrl : ''">

                                        <!-- New Video Preview -->
                                        <div x-show="videoPreviewUrl"
                                            class="relative group mt-2 max-w-full md:max-w-md" style="display: none;">
                                            <div class="relative">
                                                <video :src="videoPreviewUrl" controls
                                                    class="w-full h-auto rounded-md border border-gray-300 dark:border-gray-600"></video>
                                                <button type="button" @click="removeVideo"
                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none shadow-md transition-transform transform hover:scale-110 z-10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-1 flex justify-between items-start">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[70%]"
                                                    x-text="videoFileName"></span>
                                                <div class="text-xs text-gray-500" x-html="getCompressionInfo()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <x-input-error :messages="$errors->get($field->name)" class="mt-2" />
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const reportForm = document.getElementById('report-form-edit');
                const submitButton = document.querySelector(
                    '#report-form-edit button[type="submit"]'); // Select the primary button
                const buttonText = submitButton.querySelector('span');
                const loadingSpinner = submitButton.querySelector('svg');

                if (reportForm && submitButton) {
                    reportForm.addEventListener('submit', function() {
                        submitButton.setAttribute('disabled', 'true');
                        buttonText.textContent = 'Memperbarui...';
                        loadingSpinner.classList.remove('hidden');
                    });
                }
            });
        </script>
    @endpush

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fileEditHandler', (fieldName, existingImages) => ({
                existingImages: existingImages || [],
                deletedImages: [],
                newFiles: [],
                newImages: [],

                init() {
                    // Handle legacy string format or array format
                    if (typeof this.existingImages === 'string') {
                        this.existingImages = [this.existingImages];
                    } else if (!Array.isArray(this.existingImages)) {
                        this.existingImages = [];
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
                    const totalAfterAdd = currentActiveCount + this.newImages.length + incomingFiles
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
                isCompressing: false,
                compressionProgress: '',
                compressionPercent: 0,
                compressionMetadata: null,

                markForDeletion() {
                    this.isMarkedForDeletion = true;
                },

                async handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('video/')) {
                        // Check if compression is supported
                        if (!window.VideoCompressor || !window.VideoCompressor.isSupported()) {
                            // Fallback: use original file
                            this.videoFile = file;
                            this.videoPreviewUrl = URL.createObjectURL(file);
                            this.videoFileName = file.name;
                            return;
                        }

                        try {
                            this.isCompressing = true;
                            this.compressionProgress = 'Memulai kompresi...';
                            this.compressionPercent = 0;

                            // Compress video
                            const result = await window.VideoCompressor.compress(file, {
                                maxWidth: 1280,
                                maxHeight: 720,
                                videoBitrate: 1500000, // 1.5 Mbps for better compression
                                onProgress: (message, percent) => {
                                    this.compressionProgress = message;
                                    this.compressionPercent = percent;
                                }
                            });

                            this.isCompressing = false;

                            // Create new file from compressed blob
                            const compressedFile = new File(
                                [result.blob],
                                file.name.replace(/\.[^/.]+$/, '.webm'), {
                                    type: result.blob.type
                                }
                            );

                            // Assign compressed file
                            this.videoFile = compressedFile;
                            this.videoPreviewUrl = URL.createObjectURL(compressedFile);
                            this.videoFileName = file.name;
                            this.compressionMetadata = result.metadata;

                            // Update form input with compressed file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(compressedFile);
                            document.getElementById(fieldName + '_actual').files = dataTransfer
                                .files;

                        } catch (error) {
                            console.error('Compression error:', error);
                            this.isCompressing = false;

                            // Fallback to original
                            this.videoFile = file;
                            this.videoPreviewUrl = URL.createObjectURL(file);
                            this.videoFileName = file.name;

                            // Update form input with original file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            document.getElementById(fieldName + '_actual').files = dataTransfer
                                .files;
                        }
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
                    this.compressionMetadata = null;
                    document.getElementById(fieldName + '_actual').value = '';
                },

                getCompressionInfo() {
                    if (!this.compressionMetadata) return '';

                    const meta = this.compressionMetadata;

                    if (meta.skipped) {
                        return `<span class="text-blue-600">ℹ️ ${meta.reason}</span>`;
                    }

                    const originalSize = window.VideoCompressor.formatFileSize(meta.originalSize);
                    const compressedSize = window.VideoCompressor.formatFileSize(meta.compressedSize);
                    const ratio = meta.compressionRatio;

                    return `
                        ${originalSize} → ${compressedSize} 
                        <span class="text-green-600 font-medium">(${ratio}% lebih kecil)</span>
                    `;
                }
            }));
        });
    </script>
</x-app-layout>
