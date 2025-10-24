<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Jenis Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('report-types.update', $reportType->id) }}">
                        @csrf
                        @method('PUT') {{-- Penting untuk metode update --}}

                        <!-- Nama Jenis Laporan -->
                        <div>
                            <x-input-label for="name" :value="__('Nama Jenis Laporan')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name', $reportType->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Deskripsi -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Deskripsi')" />
                            <textarea id="description" name="description"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $reportType->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Fields Schema (JSON) -->
                        <div class="mt-4">
                            <x-input-label for="fields_schema" :value="__('Skema Field (JSON)')" />
                            <textarea id="fields_schema" name="fields_schema" rows="10"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>{{ old('fields_schema', json_encode($reportType->fields_schema, JSON_PRETTY_PRINT)) }}</textarea>
                            <x-input-error :messages="$errors->get('fields_schema')" class="mt-2" />
                            <p class="text-sm text-gray-600 mt-1">Contoh: `[{"name": "patrol_date", "label": "Tanggal
                                Patroli", "type": "date",
                                "required": true}, {"name": "route_name", "label": "Nama Rute", "type": "text",
                                "required": true}]`</p>
                        </div>

                        <!-- Is Active -->
                        <div class="mt-4 flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $reportType->is_active) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <x-input-label for="is_active" :value="__('Aktif')" class="ms-2" />
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Jenis Laporan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
