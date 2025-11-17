<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Absensi') }}
            </h2>
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:bg-gray-50 active:text-gray-800 transition ease-in-out duration-150 mr-2">
                    {{ __('Kembali ke Dashboard') }}
                </a>
                @can('create', App\Models\Attendance::class)
                <a href="{{ route('attendances.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('+ Absensi Baru') }}
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Masuk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto Masuk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Masuk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Pulang</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto Pulang</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Pulang</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($attendances as $attendance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="Nama">{{ $attendance->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Tipe">{{ $attendance->type ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Status">
                                            @if($attendance->status == 'Terlambat')
                                                <span class="text-red-500 font-semibold">{{ $attendance->status }}</span>
                                            @else
                                                {{ $attendance->status }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Waktu Masuk">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Foto Masuk">
                                            @if($attendance->photo_in_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                                <a class="open-photo-modal cursor-pointer" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}" alt="Foto Masuk" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Lokasi Masuk">
                                            <a href="https://www.openstreetmap.org/?mlat={{ $attendance->latitude_in }}&mlon={{ $attendance->longitude_in }}#map=16/{{ $attendance->latitude_in }}/{{ $attendance->longitude_in }}" target="_blank" class="text-blue-500 hover:underline">
                                                Lihat Peta
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Waktu Pulang">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Foto Pulang">
                                            @if($attendance->photo_out_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                                <a class="open-photo-modal cursor-pointer" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}" alt="Foto Pulang" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Lokasi Pulang">
                                            @if($attendance->latitude_out && $attendance->longitude_out)
                                                <a href="https://www.openstreetmap.org/?mlat={{ $attendance->latitude_out }}&mlon={{ $attendance->longitude_out }}#map=16/{{ $attendance->latitude_out }}/{{ $attendance->longitude_out }}" target="_blank" class="text-blue-500 hover:underline">
                                                    Lihat Peta
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Belum ada data absensi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media screen and (max-width: 640px) {
            table {
                border: 0;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0; /* gray-200 */
                display: block;
                border-radius: 0.5rem; /* sm:rounded-lg */
                overflow: hidden;
            }

            td {
                display: block;
                text-align: right;
                padding-left: 50%; /* Make space for the label */
                position: relative;
                border-bottom: 1px solid #e2e8f0; /* gray-200 */
            }

            td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 0.5rem;
                font-weight: bold;
                text-align: left;
            }

            td:last-child {
                border-bottom: 0;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.open-photo-modal').forEach(item => {
                item.addEventListener('click', event => {
                    event.preventDefault();
                    const imageUrl = event.currentTarget.dataset.fullImageUrl;
                    Swal.fire({
                        title: 'Foto Absensi',
                        imageUrl: imageUrl,
                        imageAlt: 'Foto Absensi',
                        showCloseButton: true,
                        showConfirmButton: false,
                        width: '80%', // Adjust width as needed
                        imageWidth: '100%',
                        imageHeight: 'auto',
                        customClass: {
                            image: 'rounded-lg'
                        }
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
