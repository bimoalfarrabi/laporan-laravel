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

                        <!-- Dynamic Fields Builder -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-3">Field Laporan</h3>
                            <div id="fields-container" class="space-y-4">
                                <!-- Field templates will be added here by JavaScript -->
                            </div>
                            <button type="button" id="add-field" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Tambah Field
                            </button>
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fieldsContainer = document.getElementById('fields-container');
            const addFieldButton = document.getElementById('add-field');
            let fieldCounter = 0;

            const fieldTypes = @json($fieldTypes);
            const existingFields = @json($reportType->reportTypeFields);

            function addField(field = {}) {
                const newFieldId = `field-${fieldCounter++}`;
                const fieldHtml = `
                    <div class="field-item p-4 border rounded-md bg-gray-50 relative">
                        <button type="button" class="remove-field absolute top-1 right-1 text-red-500 hover:text-red-700 z-10">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="${newFieldId}-label" value="Label" />
                                <x-text-input id="${newFieldId}-label" class="block mt-1 w-full" type="text" name="fields[${newFieldId}][label]" value="${field.label || ''}" required />
                            </div>
                            <div>
                                <x-input-label for="${newFieldId}-name" value="Nama Field (snake_case)" />
                                <x-text-input id="${newFieldId}-name" class="block mt-1 w-full" type="text" name="fields[${newFieldId}][name]" value="${field.name || ''}" required />
                            </div>
                            <div>
                                <x-input-label for="${newFieldId}-type" value="Tipe Field" />
                                <select id="${newFieldId}-type" name="fields[${newFieldId}][type]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    ${fieldTypes.map(type => `<option value="${type}" ${field.type === type ? 'selected' : ''}>${type}</option>`).join('')}
                                </select>
                            </div>
                            <div class="flex items-center mt-6">
                                <input type="checkbox" id="${newFieldId}-required" name="fields[${newFieldId}][required]" value="1" ${field.required ? 'checked' : ''} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <x-input-label for="${newFieldId}-required" value="Wajib Diisi" class="ms-2" />
                            </div>
                            <input type="hidden" name="fields[${newFieldId}][order]" class="field-order" value="${field.order || 0}">
                            ${field.id ? `<input type="hidden" name="fields[${newFieldId}][id]" value="${field.id}">` : ''}
                        </div>
                    </div>
                `;
                fieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
                updateFieldOrder();
            }

            function updateFieldOrder() {
                Array.from(fieldsContainer.children).forEach((item, index) => {
                    item.querySelector('.field-order').value = index;
                });
            }

            addFieldButton.addEventListener('click', () => addField());

            fieldsContainer.addEventListener('click', function (event) {
                if (event.target.closest('.remove-field')) {
                    event.target.closest('.field-item').remove();
                    updateFieldOrder();
                }
            });

            // Pre-populate fields for edit mode
            existingFields.forEach(field => addField(field));

            // Initial field order update
            updateFieldOrder();
        });
    </script>
    @endpush
</x-app-layout>