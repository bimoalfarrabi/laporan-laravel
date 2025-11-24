<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Laporan Baru: ') . $reportType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="report_type_id" value="{{ $reportType->id }}">

                        @foreach ($reportType->reportTypeFields as $field)
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
                                        name="{{ $field->name }}" value="{{ old($field->name) }}"
                                        {{ $field->required ? 'required' : '' }} />
                                @elseif ($field->type === 'textarea')
                                    <textarea id="{{ $field->name }}" name="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ $field->required ? 'required' : '' }}>{{ old($field->name) }}</textarea>
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
                                        <input id="{{ $field->name }}" type="file" multiple accept="image/*"
                                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            @change="handleFileSelect" />

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
                                    <div x-data="videoUploadHandler('{{ $field->name }}')" class="space-y-2">
                                        <input id="{{ $field->name }}_display" type="file" accept="video/*"
                                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            @change="handleFileSelect" />
                                        <input type="file" name="{{ $field->name }}" id="{{ $field->name }}" class="hidden">
                                        <div class="mt-2" x-show="videoUrl">
                                            <video :src="videoUrl" controls class="w-full rounded-md border border-gray-300"></video>
                                            <button type="button" @click="removeVideo"
                                                class="mt-1 text-sm text-red-600 hover:text-red-800">Hapus Video</button>
                                        </div>
                                        <p class="text-sm text-gray-500">Maksimal 1 video.</p>
                                    </div>
                                @endif
                                <x-input-error :messages="$errors->get($field->name)" class="mt-2" />
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Simpan Laporan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

                        this.files.push(file);
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.images.push({
                                url: e.target.result,
                                file: file
                            });
                        };
                        reader.readAsDataURL(file);
                    });

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
                    this.files.forEach(file => dataTransfer.items.add(file));
                    document.getElementById(fieldName + '_actual').files = dataTransfer.files;
                }
            }));

            Alpine.data('videoUploadHandler', (fieldName) => ({
                videoUrl: null,
                videoFile: null,
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file && file.type.startsWith('video/')) {
                        this.videoFile = file;
                        this.videoUrl = URL.createObjectURL(file);
                    } else {
                        this.videoFile = null;
                        this.videoUrl = null;
                    }
                    this.updateActualInput();
                },
                removeVideo() {
                    this.videoFile = null;
                    this.videoUrl = null;
                    document.getElementById(fieldName + '_display').value = '';
                    this.updateActualInput();
                },
                updateActualInput() {
                    const dataTransfer = new DataTransfer();
                    if (this.videoFile) {
                        dataTransfer.items.add(this.videoFile);
                    }
                    document.getElementById(fieldName).files = dataTransfer.files;
                }
            }));
        });
    </script>
</x-app-layout>
