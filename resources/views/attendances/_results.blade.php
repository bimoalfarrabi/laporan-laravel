@if ($attendances->isEmpty())
    <div class="text-center py-10">
        <p class="text-gray-500">Tidak ada data absensi atau izin yang ditemukan untuk tanggal ini.</p>
    </div>
@else
    {{-- Table View for Larger Screens --}}
    <div class="mt-6 overflow-x-auto hidden sm:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                        Masuk</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto
                        Masuk</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi
                        Masuk</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                        Pulang</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto
                        Pulang</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi
                        Pulang</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($attendances as $attendance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $attendance->user->name }}
                            @if ($attendance->user->roles->isNotEmpty())
                                <span
                                    class="text-sm text-gray-500">({{ $attendance->user->roles->first()->name }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->type ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($attendance->status == 'Izin' && isset($attendance->leaveRequest))
                                <a href="{{ route('leave-requests.show', $attendance->leaveRequest->id) }}"
                                    class="text-blue-500 hover:underline font-semibold">
                                    Izin
                                </a>
                            @elseif($attendance->status == 'Terlambat')
                                <span class="text-red-500 font-semibold">{{ $attendance->status }}</span>
                            @else
                                {{ $attendance->status }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if (isset($attendance->photo_in_path) &&
                                    $attendance->photo_in_path &&
                                    Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                <a class="open-photo-modal cursor-pointer"
                                    data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}">
                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}"
                                        alt="Foto Masuk" class="h-10 w-10 rounded-full object-cover">
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if (isset($attendance->latitude_in) && $attendance->latitude_in)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}"
                                    target="_blank" class="text-blue-500 hover:underline">
                                    Lihat Peta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if (isset($attendance->photo_out_path) &&
                                    $attendance->photo_out_path &&
                                    Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                <a class="open-photo-modal cursor-pointer"
                                    data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}">
                                    <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}"
                                        alt="Foto Pulang" class="h-10 w-10 rounded-full object-cover">
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if (isset($attendance->latitude_out) && $attendance->longitude_out)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_out }},{{ $attendance->longitude_out }}"
                                    target="_blank" class="text-blue-500 hover:underline">
                                    Lihat Peta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Card View for Small Screens --}}
    <div class="mt-6 sm:hidden space-y-4">
        @foreach ($attendances as $attendance)
            <div class="bg-white p-4 shadow-md rounded-lg border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-bold text-lg text-gray-800">{{ $attendance->user->name }}</div>
                        @if ($attendance->user->roles->isNotEmpty())
                            <div class="text-sm text-gray-500">({{ $attendance->user->roles->first()->name }})</div>
                        @endif
                    </div>
                    <div class="text-right">
                        @if ($attendance->status == 'Izin' && isset($attendance->leaveRequest))
                            <a href="{{ route('leave-requests.show', $attendance->leaveRequest->id) }}"
                                class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-blue-200 text-blue-800 text-xs hover:underline">
                                Izin
                            </a>
                        @elseif($attendance->status == 'Terlambat')
                            <span
                                class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-red-200 text-red-800 text-xs">{{ $attendance->status }}</span>
                        @else
                            <span
                                class="px-2 py-1 inline-flex leading-5 font-semibold rounded-full bg-green-200 text-green-800 text-xs">{{ $attendance->status }}</span>
                        @endif
                        <div class="text-xs text-gray-500 mt-1">{{ $attendance->type ?? 'N/A' }}</div>
                    </div>
                </div>

                @if ($attendance->status != 'Izin')
                    <div class="border-t border-gray-200 pt-3 space-y-3 text-sm">
                        <div class="flex items-start">
                            <strong class="text-gray-600 w-1/3">Masuk:</strong>
                            <div class="w-2/3">
                                <p>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}
                                </p>
                                <div class="flex items-center mt-1">
                                    @if (isset($attendance->photo_in_path) &&
                                            $attendance->photo_in_path &&
                                            Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_in_path))
                                        <a class="open-photo-modal cursor-pointer mr-2"
                                            data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}">
                                            <img src="{{ route('files.serve', ['filePath' => $attendance->photo_in_path]) }}"
                                                alt="Foto Masuk" class="h-10 w-10 rounded-md object-cover">
                                        </a>
                                    @endif
                                    @if (isset($attendance->latitude_in) && $attendance->latitude_in)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}"
                                            target="_blank" class="text-blue-500 hover:underline">
                                            Lihat Lokasi
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <strong class="text-gray-600 w-1/3">Pulang:</strong>
                            <div class="w-2/3">
                                <p>{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('d M Y, H:i') : '-' }}
                                </p>
                                @if ($attendance->time_out)
                                    <div class="flex items-center mt-1">
                                        @if (isset($attendance->photo_out_path) &&
                                                $attendance->photo_out_path &&
                                                Illuminate\Support\Facades\Storage::disk('public')->exists($attendance->photo_out_path))
                                            <a class="open-photo-modal cursor-pointer mr-2"
                                                data-full-image-url="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}">
                                                <img src="{{ route('files.serve', ['filePath' => $attendance->photo_out_path]) }}"
                                                    alt="Foto Pulang" class="h-10 w-10 rounded-md object-cover">
                                            </a>
                                        @endif
                                        @if (isset($attendance->latitude_out) && $attendance->longitude_out)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance->latitude_out }},{{ $attendance->longitude_out }}"
                                                target="_blank" class="text-blue-500 hover:underline">
                                                Lihat Lokasi
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
@endif
