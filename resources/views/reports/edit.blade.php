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

                        @foreach ($report->reportType->fields_schema as $field)
                            <div class="mt-4">
                                <x-input-label for="{{ $field['name'] }}" :value="__($field['label'])" />

                                @if ($field['type'] === 'text' || $field['type'] === 'date' || $field['type'] === 'time' || $field['type'] === 'number')
                                    <input id="{{ $field['name'] }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                        value="{{ old($field['name']) }}"
                                        {{ isset($field['required']) && $field['required'] ? 'required' : '' }} />
                                @elseif ($field['type'] === 'textarea')
                                    <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>{{ old($field['name']) }}</textarea>
                                @elseif ($field['type'] === 'select')
                                    <select id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        <option value="">Pilih {{ $field['label'] }}</option>
                                        @foreach ($field['options'] as $option)
                                            <option value="{{ $option['value'] }}"
                                                {{ old($field['name']) == $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif ($field['type'] === 'checkbox')
                                    <input type="checkbox" id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                        value="1" {{ old($field['name']) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                @elseif ($field['type'] === 'file')
                                    {{-- Tambahkan kondisi untuk type file --}}
                                    @if (isset($report->data[$field['name']]) && $report->data[$field['name']])
                                        <div class="mb-2">
                                            <p>{{ __('File saat ini:') }} <a
                                                    href="{{ Storage::url($report->data[$field['name']]) }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline">{{ basename($report->data[$field['name']]) }}</a>
                                            </p>
                                            <img src="{{ Storage::url($report->data[$field['name']]) }}"
                                                alt="Bukti Foto" class="h-20 w-auto object-cover rounded-md mt-1">
                                        </div>
                                    @endif
                                    <input id="{{ $field['name'] }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="file" name="{{ $field['name'] }}"
                                        {{ isset($field['required']) && $field['required'] && !isset($report->data[$field['name']]) ? 'required' : '' }} />
                                    @if ($field['required'] && isset($report->data[$field['name']]))
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ __('Kosongkan jika tidak ingin mengubah file.') }}</p>
                                    @endif
                                    <x-input-error :messages="$errors->get($field['name'])" class="mt-2" />
                                @endif
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
</x-app-layout>
