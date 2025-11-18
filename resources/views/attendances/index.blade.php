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

                    <div class="overflow-x-auto hidden sm:block">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $attendance->user->name }}
                                            @if ($attendance->user->roles->isNotEmpty())
                                                <span class="text-sm text-gray-500">({{ $attendance->user->roles->first()->name }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->type ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->status == 'Terlambat')
                                                <span class="text-red-500 font-semibold">{{ $attendance->status }}</span>
                                            @else
                                                {{ $attendance->status }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->photo_in_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                                <a class="open-photo-modal cursor-pointer" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}" alt="Foto Masuk" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}" target="_blank" class="text-blue-500 hover:underline">
                                                Lihat Peta
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->photo_out_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                                <a class="open-photo-modal cursor-pointer" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}" alt="Foto Pulang" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->latitude_out && $attendance->longitude_out)
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_out }},{{ $attendance->longitude_out }}" target="_blank" class="text-blue-500 hover:underline">
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

                    {{-- Card View for Small Screens --}}
                    <div class="mt-6 sm:hidden space-y-4">
                        @forelse ($attendances as $attendance)
                            <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                                {{-- Card Header: User and Status --}}
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="font-bold text-lg text-gray-800">{{ $attendance->user->name }}</div>
                                        @if ($attendance->user->roles->isNotEmpty())
                                            <div class="text-sm text-gray-500">({{ $attendance->user->roles->first()->name }})</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        @if($attendance->status == 'Terlambat')
                                            <span class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-red-200 text-red-800 text-xs">{{ $attendance->status }}</span>
                                        @else
                                            <span class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-green-200 text-green-800 text-xs">{{ $attendance->status }}</span>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">{{ $attendance->type ?? 'N/A' }}</div>
                                    </div>
                                </div>

                                {{-- Card Body: Details --}}
                                <div class="border-t border-gray-200 pt-3 space-y-3 text-sm">
                                    {{-- Clock In --}}
                                    <div class="flex items-start">
                                        <strong class="text-gray-600 w-1/3">Masuk:</strong>
                                        <div class="w-2/3">
                                            <p>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}</p>
                                            <div class="flex items-center mt-1">
                                                @if($attendance->photo_in_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                                    <a class="open-photo-modal cursor-pointer mr-2" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}">
                                                        <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}" alt="Foto Masuk" class="h-10 w-10 rounded-md object-cover">
                                                    </a>
                                                @endif
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}" target="_blank" class="text-blue-500 hover:underline">
                                                    Lihat Lokasi
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Clock Out --}}
                                    <div class="flex items-start">
                                        <strong class="text-gray-600 w-1/3">Pulang:</strong>
                                        <div class="w-2/3">
                                            <p>{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}</p>
                                            @if($attendance->time_out)
                                                <div class="flex items-center mt-1">
                                                    @if($attendance->photo_out_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                                        <a class="open-photo-modal cursor-pointer mr-2" data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}">
                                                            <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}" alt="Foto Pulang" class="h-10 w-10 rounded-md object-cover">
                                                        </a>
                                                    @endif
                                                    @if($attendance->latitude_out && $attendance->longitude_out)
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_out }},{{ $attendance->longitude_out }}" target="_blank" class="text-blue-500 hover:underline">
                                                            Lihat Lokasi
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <p class="text-gray-500">Belum ada data absensi.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

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
                        width: '50%', // Adjust width as needed
                        imageWidth: 'auto',
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
