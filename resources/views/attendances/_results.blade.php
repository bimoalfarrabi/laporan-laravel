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
                    @php
                    $columns = [
                        'user.name' => 'Nama',
                        'type' => 'Tipe',
                        'status' => 'Status',
                        'time_in' => 'Waktu Masuk',
                    ];
                    @endphp

                    @foreach ($columns as $column => $title)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('attendances.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => ($sortBy == $column && $sortDirection == 'asc') ? 'desc' : 'asc'])) }}" class="flex items-center">
                                {{ $title }}
                                @if ($sortBy == $column)
                                    @if ($sortDirection == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                    @endforeach

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto
                        Masuk</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi
                        Masuk</th>
                    
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ route('attendances.index', array_merge(request()->query(), ['sort_by' => 'time_out', 'sort_direction' => ($sortBy == 'time_out' && $sortDirection == 'asc') ? 'desc' : 'asc'])) }}" class="flex items-center">
                            Waktu Pulang
                            @if ($sortBy == 'time_out')
                                @if ($sortDirection == 'asc')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                @else
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                @endif
                            @endif
                        </a>
                    </th>

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
                            @if ($attendance->time_in && $attendance->status != 'Izin')
                                @php
                                    $time_in = \Carbon\Carbon::parse($attendance->time_in);
                                    $expected_start_hour = ($time_in->hour < 14) ? 7 : 19;
                                    $expected_start_time = $time_in->copy()->setTime($expected_start_hour, 0, 0);
                                    $diff_minutes = $expected_start_time->diffInMinutes($time_in, false);
                                    $diff_formatted = ($diff_minutes >= 0 ? '+' : '-') . gmdate('H:i', abs($diff_minutes) * 60);
                                    $color_class = $diff_minutes > 0 ? 'text-red-500' : 'text-green-500';
                                    if ($diff_minutes === 0) {
                                        $diff_formatted = '00:00';
                                        $color_class = 'text-gray-500';
                                    }
                                @endphp
                                {{ $time_in->format('d M Y, H:i') }}
                                <div class="text-xs {{ $color_class }} font-semibold">{{ $diff_formatted }}</div>
                            @else
                                {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}
                            @endif
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
                                <a href="https://www.google.com/maps/@{{ $attendance->latitude_in }},{{ $attendance->longitude_in }},18z"
                                    target="_blank" class="text-blue-500 hover:underline">
                                    Lihat Peta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($attendance->time_out)
                                @php
                                    $time_out = \Carbon\Carbon::parse($attendance->time_out);
                                    $time_in_for_calc = \Carbon\Carbon::parse($attendance->time_in);
                                    $diff_minutes_out = 0;
                                    $diff_formatted_out = '';
                                    $color_class_out = 'text-gray-500';
                                    $expected_end_time = null;

                                    if ($attendance->type == 'Reguler') {
                                        $expected_end_time = $time_in_for_calc->copy()->setTime(15, 0, 0);
                                    } elseif ($attendance->type == 'Normal Pagi') {
                                        $expected_end_time = $time_in_for_calc->copy()->setTime(19, 0, 0);
                                    } elseif ($attendance->type == 'Normal Malam') {
                                        $expected_end_time = $time_in_for_calc->copy()->addDay()->setTime(7, 0, 0);
                                    }

                                    if ($expected_end_time) {
                                        $diff_minutes_out = $expected_end_time->diffInMinutes($time_out, false);
                                        $diff_formatted_out = ($diff_minutes_out >= 0 ? '+' : '-') . gmdate('H:i', abs($diff_minutes_out) * 60);
                                        $color_class_out = $diff_minutes_out >= 0 ? 'text-green-500' : 'text-red-500';
                                        if ($diff_minutes_out === 0) {
                                            $diff_formatted_out = '00:00';
                                            $color_class_out = 'text-gray-500';
                                        }
                                    }
                                @endphp
                                {{ $time_out->format('d M Y, H:i') }}
                                @if ($attendance->type && $expected_end_time)
                                    <div class="text-xs {{ $color_class_out }} font-semibold">{{ $diff_formatted_out }}</div>
                                @endif
                            @else
                                -
                            @endif
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
                                <a href="https://www.google.com/maps/@{{ $attendance->latitude_out }},{{ $attendance->longitude_out }},18z"
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
                                <p>
                                    @if ($attendance->time_in && $attendance->status != 'Izin')
                                        @php
                                            $time_in_card = \Carbon\Carbon::parse($attendance->time_in);
                                            $expected_start_hour_card = ($time_in_card->hour < 14) ? 7 : 19;
                                            $expected_start_time_card = $time_in_card->copy()->setTime($expected_start_hour_card, 0, 0);
                                            $diff_minutes_card = $expected_start_time_card->diffInMinutes($time_in_card, false);
                                            $diff_formatted_card = ($diff_minutes_card >= 0 ? '+' : '-') . gmdate('H:i', abs($diff_minutes_card) * 60);
                                            $color_class_card = $diff_minutes_card > 0 ? 'text-red-500' : 'text-green-500';
                                            if ($diff_minutes_card === 0) {
                                                $diff_formatted_card = '00:00';
                                                $color_class_card = 'text-gray-500';
                                            }
                                        @endphp
                                        {{ $time_in_card->format('d M Y, H:i') }}
                                        <span class="block text-xs {{ $color_class_card }} font-semibold">{{ $diff_formatted_card }}</span>
                                    @else
                                        {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('d M Y, H:i') : '-' }}
                                    @endif
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
                                        <a href="https://www.google.com/maps/@{{ $attendance->latitude_in }},{{ $attendance->longitude_in }},18z"
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
                                <p>
                                    @if ($attendance->time_out)
                                        @php
                                            $time_out_card = \Carbon\Carbon::parse($attendance->time_out);
                                            $time_in_for_calc_card = \Carbon\Carbon::parse($attendance->time_in);
                                            $diff_minutes_out_card = 0;
                                            $diff_formatted_out_card = '';
                                            $color_class_out_card = 'text-gray-500';
                                            $expected_end_time_card = null;
                                            if ($attendance->type == 'Reguler') {
                                                $expected_end_time_card = $time_in_for_calc_card->copy()->setTime(15, 0, 0);
                                            } elseif ($attendance->type == 'Normal Pagi') {
                                                $expected_end_time_card = $time_in_for_calc_card->copy()->setTime(19, 0, 0);
                                            } elseif ($attendance->type == 'Normal Malam') {
                                                $expected_end_time_card = $time_in_for_calc_card->copy()->addDay()->setTime(7, 0, 0);
                                            }
                                            if ($expected_end_time_card) {
                                                $diff_minutes_out_card = $expected_end_time_card->diffInMinutes($time_out_card, false);
                                                $diff_formatted_out_card = ($diff_minutes_out_card >= 0 ? '+' : '-') . gmdate('H:i', abs($diff_minutes_out_card) * 60);
                                                $color_class_out_card = $diff_minutes_out_card >= 0 ? 'text-green-500' : 'text-red-500';
                                                if ($diff_minutes_out_card === 0) {
                                                    $diff_formatted_out_card = '00:00';
                                                    $color_class_out_card = 'text-gray-500';
                                                }
                                            }
                                        @endphp
                                        {{ $time_out_card->format('d M Y, H:i') }}
                                        @if ($attendance->type && $expected_end_time_card)
                                            <span class="block text-xs {{ $color_class_out_card }} font-semibold">{{ $diff_formatted_out_card }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
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
                                            <a href="https://www.google.com/maps/@{{ $attendance->latitude_out }},{{ $attendance->longitude_out }},18z"
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
