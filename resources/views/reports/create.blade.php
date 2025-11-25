<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Laporan Baru: ') . $reportType->name }}
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="report-form-create" method="POST" action="{{ route('reports.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="report_type_id" value="{{ $reportType->id }}">

                        @foreach ($reportType->reportTypeFields as $field)
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
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="{{ $field->type === 'role_specific_text' ? 'text' : $field->type }}"
                                        name="{{ $field->name }}" value="{{ old($field->name) }}"
                                        {{ $field->required ? 'required' : '' }} />
                                @elseif ($field->type === 'textarea')
                                    <input id="{{ $field->name }}_input" type="hidden" name="{{ $field->name }}"
                                        value="{{ old($field->name) }}">
                                    <trix-editor id="{{ $field->name }}" input="{{ $field->name }}_input"
                                        class="trix-content block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-h-[150px]"></trix-editor>
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
                                        value="1" {{ old($field->name) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                @elseif ($field->type === 'file')
                                    <div x-data="fileUploadHandler('{{ $field->name }}')" class="space-y-2">
                                        <!-- Hidden file input -->
                                        <input id="{{ $field->name }}" type="file" multiple accept="image/*"
                                            class="hidden" @change="handleFileSelect" />

                                        <!-- Custom file upload button -->
                                        <label for="{{ $field->name }}"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 hover:border-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 transition ease-in-out duration-150 cursor-pointer group">
                                            <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span
                                                class="group-hover:text-indigo-600 transition-colors duration-150">Pilih
                                                Foto</span>
                                        </label>

                                        {{-- Hidden input to store actual files for form submission (managed by DataTransfer in JS) --}}
                                        <input type="file" name="{{ $field->name }}[]"
                                            id="{{ $field->name }}_actual" multiple class="hidden">

                                        <div class="grid grid-cols-3 gap-4 mt-2" id="{{ $field->name }}_preview">
                                            <template x-for="(image, index) in images" :key="index">
                                                <div class="relative group">
                                                    <img :src="image.url"
                                                        class="w-full h-24 object-cover rounded-md border border-gray-300">
                                                    <button type="button" @click="removeImage(index)"
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
                                    <div x-data="videoUploadHandler('{{ $field->name }}')" class="space-y-3">
                                        <!-- Hidden file input -->
                                        <input id="{{ $field->name }}" type="file" accept="video/*" class="hidden"
                                            @change="handleFileSelect" />

                                        <!-- Custom file upload button -->
                                        <label for="{{ $field->name }}"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 hover:border-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 transition ease-in-out duration-150 cursor-pointer group">
                                            <svg class="w-4 h-4 mr-2 text-gray-500 group-hover:text-indigo-600 transition-colors duration-150"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span
                                                class="group-hover:text-indigo-600 transition-colors duration-150">Pilih
                                                Video</span>
                                        </label>
                                        <input type="file" name="{{ $field->name }}"
                                            id="{{ $field->name }}_actual" class="hidden">

                                        {{-- Compression Loading Overlay --}}
                                        <div x-show="isCompressing" style="display: none;"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                            <div class="bg-white rounded-lg p-6 max-w-md mx-4">
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
                                                        <h3 class="text-lg font-medium text-gray-900">Mengompresi Video
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

                                        <!-- Video Preview -->
                                        <div x-show="videoUrl" class="relative group mt-2" style="display: none;">
                                            <div
                                                class="bg-gray-100 rounded-lg p-2 border border-gray-300 max-w-full md:max-w-md md:mr-auto mx-auto md:mx-0">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700 truncate"
                                                        x-text="videoFileName"></span>
                                                    <button type="button" @click="removeVideo"
                                                        class="text-red-500 hover:text-red-700 focus:outline-none">
                                                        <svg class="w-5 h-5 fill-none stroke-currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <video :src="videoUrl" controls
                                                    class="w-full h-auto rounded-md"></video>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500" x-html="getCompressionInfo()">
                                            </div>
                                        </div>


                                    </div>
                                @endif
                                <x-input-error :messages="$errors->get($field->name)" class="mt-2" />
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan') }}
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
                const reportForm = document.getElementById('report-form-create');
                const submitButton = document.getElementById('submit-report-button');
                const buttonText = document.getElementById('button-text');
                const loadingSpinner = document.getElementById('loading-spinner');

                if (reportForm && submitButton) {
                    reportForm.addEventListener('submit', function() {
                        submitButton.setAttribute('disabled', 'true');
                        buttonText.textContent = 'Menyimpan...';
                        loadingSpinner.classList.remove('hidden');
                    });
                }
            });
        </script>
    @endpush

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fileUploadHandler', (fieldName) => ({
                images: [],
                files: [],
                init() {
                    // No initial files for create
                },
                handleFileSelect(event) {
                    const newFiles = Array.from(event.target.files);
                    const totalFiles = this.files.length + newFiles.length;

                    if (totalFiles > 3) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Maksimal 3 gambar yang diperbolehkan.',
                        });
                        event.target.value = ''; // Reset input
                        return;
                    }

                    newFiles.forEach(file => {
                        if (!file.type.startsWith('image/')) return;

                        this.files.push({
                            file: file,
                            url: URL.createObjectURL(file)
                        });
                    });

                    this.images = this.files; // Update images for rendering
                    this.updateActualInput();
                    event.target.value = ''; // Reset input to allow selecting same file again if needed
                },
                removeImage(index) {
                    this.images.splice(index, 1);
                    this.files.splice(index, 1);
                    this.updateActualInput();
                },
                updateActualInput() {
                    const dataTransfer = new DataTransfer();
                    this.files.forEach(file => dataTransfer.items.add(file.file));
                    document.getElementById(fieldName + '_actual').files = dataTransfer.files;
                }
            }));

            Alpine.data('videoUploadHandler', (fieldName) => ({
                videoUrl: null,
                videoFile: null,
                videoFileName: '',
                isCompressing: false,
                compressionProgress: '',
                compressionPercent: 0,
                compressionMetadata: null,

                async handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('video/')) {
                        // Check if compression is supported
                        if (!window.VideoCompressor || !window.VideoCompressor.isSupported()) {
                            // Fallback: use original file without compression
                            this.videoFile = file;
                            this.videoUrl = URL.createObjectURL(file);
                            this.videoFileName = file.name;
                            this.updateActualInput();
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
                                file.name.replace(/\.[^/.]+$/,
                                    '.webm'), // Change extension to .webm
                                {
                                    type: result.blob.type
                                }
                            );

                            this.videoFile = compressedFile;
                            this.videoUrl = URL.createObjectURL(compressedFile);
                            this.videoFileName = file.name;
                            this.compressionMetadata = result.metadata;

                        } catch (error) {
                            console.error('Compression error:', error);
                            this.isCompressing = false;

                            // Fallback to original
                            this.videoFile = file;
                            this.videoUrl = URL.createObjectURL(file);
                            this.videoFileName = file.name;
                        }
                    } else {
                        this.videoFile = null;
                        this.videoUrl = null;
                        this.videoFileName = '';
                        this.compressionMetadata = null;
                    }
                    this.updateActualInput();
                },

                removeVideo() {
                    // Revoke the object URL to free memory
                    if (this.videoUrl) {
                        URL.revokeObjectURL(this.videoUrl);
                    }
                    this.videoFile = null;
                    this.videoUrl = null;
                    this.videoFileName = '';
                    this.compressionMetadata = null;
                    document.getElementById(fieldName).value = '';
                    this.updateActualInput();
                },

                updateActualInput() {
                    const actualInput = document.getElementById(fieldName + '_actual');
                    const displayInput = document.getElementById(fieldName);

                    if (this.videoFile) {
                        // Create DataTransfer to assign file to hidden input
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(this.videoFile);
                        actualInput.files = dataTransfer.files;

                        // Clear display input to prevent original file submission
                        if (displayInput) {
                            displayInput.value = '';
                        }
                    } else {
                        // Clear both inputs
                        actualInput.value = '';
                        if (displayInput) {
                            displayInput.value = '';
                        }
                    }
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
