<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Nomor Telepon') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-6 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 shadow-md rounded-lg">
                        <p class="mb-2">1. Menghubungi Ruangan Dalam Gedung Manajemen RSUD: <b>Angkat Gagang - Tekan EXT yang akan dituju.</b></p>
                        <p class="mb-0">2. Menghubungi kantor TU (Timur) ke RSUD Blambangan: <b> Tekan 88 - Tekan EXT yang akan dituju.</b></p>
                    </div>

                    <form method="GET" action="{{ route('phone-numbers.index') }}" class="mb-4">
                        <div class="flex items-center">
                            <input type="text" name="search" placeholder="Cari nama atau ext..."
                                value="{{ $search }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <button type="submit"
                                class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cari
                            </button>
                            <a href="{{ route('phone-numbers.index') }}"
                                class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reset
                            </a>
                        </div>
                    </form>

                    @if ($phoneNumbers->isEmpty())
                        <p class="mt-4">Tidak ada nomor telepon yang ditemukan.</p>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ext
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($phoneNumbers as $phoneNumber)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $phoneNumber['id'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $phoneNumber['ext'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $phoneNumber['nama'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $phoneNumbers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
