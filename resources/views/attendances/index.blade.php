<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Absensi') }}
            </h2>
            @can('create', App\Models\Attendance::class)
            <a href="{{ route('attendances.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('+ Absensi Baru') }}
            </a>
            @endcan
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->photo_in_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                                <a href="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}" target="_blank">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}" alt="Foto Masuk" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="https://www.openstreetmap.org/?mlat={{ $attendance->latitude_in }}&mlon={{ $attendance->longitude_in }}#map=16/{{ $attendance->latitude_in }}/{{ $attendance->longitude_in }}" target="_blank" class="text-blue-500 hover:underline">
                                                Lihat Peta
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($attendance->photo_out_path && Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                                <a href="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}" target="_blank">
                                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}" alt="Foto Pulang" class="h-10 w-10 rounded-full object-cover">
                                                </a>
                                            @else
                                                <span class="text-gray-400">Foto tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
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
</x-app-layout>
