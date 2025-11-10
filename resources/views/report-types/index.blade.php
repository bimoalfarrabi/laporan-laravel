<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Jenis Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center mb-4 space-x-4">
                        @can('create', App\Models\ReportType::class)
                            <a href="{{ route('report-types.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Buat Jenis Laporan Baru
                            </a>
                        @endcan

                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($reportTypes->isEmpty())
                        <p class="mt-4">Belum ada Jenis Laporan yang dibuat.</p>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @php
                                            $columns = [
                                                'id' => 'ID',
                                                'name' => 'Nama',
                                                'slug' => 'Slug',
                                                'is_active' => 'Aktif',
                                                'created_at' => 'Waktu Dibuat',
                                            ];
                                        @endphp

                                        @foreach ($columns as $column => $title)
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ route('report-types.index', [
                                                    'sort_by' => $column,
                                                    'sort_direction' => $sortBy == $column && $sortDirection == 'asc' ? 'desc' : 'asc',
                                                ]) }}">
                                                    {{ $title }}
                                                    @if ($sortBy == $column)
                                                        @if ($sortDirection == 'asc')
                                                            <span>&#9650;</span>
                                                        @else
                                                            <span>&#9660;</span>
                                                        @endif
                                                    @endif
                                                </a>
                                            </th>
                                        @endforeach

                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($reportTypes as $type)
                                        <tr>
                                            <td class="px-6 py-4">
                                                {{ $type->id }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $type->name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $type->slug }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $type->is_active ? 'Ya' : 'Tidak' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <x-waktu-dibuat :date="$type->created_at" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('report-types.show', $type->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-2">Lihat</a>
                                                @can('update', $type)
                                                    <a href="{{ route('report-types.edit', $type->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                                                @endcan
                                                @can('delete', $type)
                                                    <form action="{{ route('report-types.destroy', $type->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                            data-confirm-dialog="true"
                                                            data-swal-title="Hapus Jenis Laporan?"
                                                            data-swal-text="Semua laporan dengan jenis ini juga akan terhapus. Anda yakin?">Hapus</button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $reportTypes->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
