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

                                @if ($field->type === 'text' || $field->type === 'date' || $field->type === 'time' || $field->type === 'number' || $field->type === 'role_specific_text')
                                    <input id="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="text" name="{{ $field->name }}"
                                        value="{{ old($field->name, $report->data[$field->name] ?? '') }}"
                                        {{ $field->required ? 'required' : '' }} />
                                @elseif ($field->type === 'textarea')
                                    <textarea id="{{ $field->name }}" name="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        {{ $field->required ? 'required' : '' }}>{{ old($field->name, $report->data[$field->name] ?? '') }}</textarea>
                                @elseif ($field->type === 'select') {{-- Assuming 'select' type will still have options --}}
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
                                        value="1" {{ old($field->name, $report->data[$field->name] ?? false) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                @elseif ($field->type === 'file')
                                    @if (isset($report->data[$field->name]) && $report->data[$field->name] && Storage::disk('public')->exists($report->data[$field->name]))
                                        <div class="mb-2">
                                            <p>{{ __('File saat ini:') }} <a
                                                    href="{{ Storage::url($report->data[$field->name]) }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline">{{ basename($report->data[$field->name]) }}</a>
                                            </p>
                                            <img src="{{ Storage::url($report->data[$field->name]) }}"
                                                alt="Bukti Foto" class="h-20 w-auto object-cover rounded-md mt-1">
                                        </div>
                                    @else
                                        <p class="text-red-500">foto telah dihapus</p>
                                    @endif
                                    <input id="{{ $field->name }}"
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        type="file" name="{{ $field->name }}"
                                        {{ $field->required && !(isset($report->data[$field->name]) && $report->data[$field->name] && Storage::disk('public')->exists($report->data[$field->name])) ? 'required' : '' }} />
                                    @if ($field->required && isset($report->data[$field->name]) && $report->data[$field->name] && Storage::disk('public')->exists($report->data[$field->name]))
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ __('Kosongkan jika tidak ingin mengubah file.') }}</p>
                                    @endif
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
</x-app-layout>