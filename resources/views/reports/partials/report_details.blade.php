<div id="report-detail-container">
    {{-- Simplified Report Metadata --}}
    <div class="space-y-4 mb-6">
        <div class="text-lg">
            <p class="text-gray-600"><strong>ID Laporan:</strong> <span class="font-mono bg-gray-100 px-2 py-1 rounded">#{{ $report->id }}</span></p>
            <p class="text-gray-600"><strong>Jenis Laporan:</strong> <span class="font-semibold text-gray-800">{{ $report->reportType->name }}</span></p>
            @if ($report->shift)
                <p class="text-gray-600"><strong>Shift:</strong> <span class="font-semibold text-gray-800">{{ $report->shift }}</span></p>
            @endif
            <p class="text-gray-600"><strong>Status:</strong>
                @php
                    $bgColor = '';
                    switch ($report->status) {
                        case 'belum disetujui':
                            $bgColor = 'bg-yellow-200 text-yellow-800';
                            break;
                        case 'disetujui':
                            $bgColor = 'bg-green-200 text-green-800';
                            break;
                        case 'ditolak':
                            $bgColor = 'bg-red-200 text-red-800';
                            break;
                        default:
                            break;
                    }
                @endphp
                <span
                    class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $bgColor }}">
                    {{ ucfirst($report->status) }}
                </span>
            </p>
        </div>
    </div>

    {{-- Signature/History Block --}}
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">Riwayat Laporan</h3>
        <div
            class="flex flex-col md:flex-row md:justify-between text-base text-gray-700 space-y-3 md:space-y-0">
            {{-- Left Side --}}
            <div class="space-y-3">
                <p><strong>Dibuat oleh:</strong> {{ $report->user->name }} @if ($report->user->roles->isNotEmpty())
                        <span class="text-sm text-gray-500">({{ $report->user->roles->first()->name }})</span>
                    @endif pada
                    <span class="font-medium">{{ $report->created_at->format('d-m-Y H:i') }}</span></p>
                @if ($report->lastEditedBy && $report->updated_at != $report->created_at)
                    <p><strong>Terakhir diperbarui oleh:</strong> {{ $report->lastEditedBy->name }}
                        @if ($report->lastEditedBy->roles->isNotEmpty())<span class="text-sm text-gray-500">({{ $report->lastEditedBy->roles->first()->name }})</span>@endif
                        pada
                        <span class="font-medium">{{ $report->updated_at->format('d-m-Y H:i') }}</span></p>
                @endif
            </div>

            {{-- Right Side --}}
            <div class="space-y-3 md:text-right">
                @if ($report->approvedBy)
                    <p><strong>Disetujui oleh:</strong> {{ $report->approvedBy->name }}
                        @if ($report->approvedBy->roles->isNotEmpty())<span class="text-sm text-gray-500">({{ $report->approvedBy->roles->first()->name }})</span>@endif pada
                        <span class="font-medium">{{ $report->approved_at->format('d-m-Y H:i') }}</span></p>
                @endif
                @if ($report->rejectedBy)
                    <p><strong>Ditolak oleh:</strong> {{ $report->rejectedBy->name }} pada
                        <span class="font-medium">{{ $report->rejected_at->format('d-m-Y H:i') }}</span></p>
                @endif
                @if ($report->deleted_at)
                    <p class="text-red-700"><strong>Dihapus oleh:</strong>
                        {{ $report->deletedBy?->name ?? 'Pengguna telah dihapus' }} pada
                        <span class="font-medium">{{ $report->deleted_at->format('d-m-Y H:i') }}</span></p>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-8">

    {{-- Report Data (single-column with dividers) --}}
    <div x-data>
        @php
            // Get the value of the 'time' or 'waktu' field to merge it with the 'date' or 'tanggal' field.
            $timeFieldValue = $report->data['time'] ?? ($report->data['waktu'] ?? null);
            $fields = $report->reportType->reportTypeFields
                ->unique('name')
                ->filter(fn($field) => !in_array($field->name, ['time', 'waktu']));
        @endphp
        <div class="space-y-8">
            @foreach ($fields as $field)
                {{-- Skip rendering the 'time' or 'waktu' field as it's merged with the 'date' or 'tanggal' field --}}
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">
                        @if ($field->name === 'date' || $field->name === 'tanggal')
                            Waktu Kejadian
                        @elseif ($field->type === 'file')
                            Lampiran Gambar
                        @else
                            {{ $field->label }}
                        @endif
                    </h4>
                    <div class="mt-2 text-base text-gray-900">
                        @if ($field->name === 'date' || $field->name === 'tanggal')
                            @php
                                $dateData = $report->data['date'] ?? ($report->data['tanggal'] ?? null);
                                $dateValue = $dateData
                                    ? Carbon\Carbon::parse($dateData)->format('d-m-Y')
                                    : null;
                            @endphp
                            <span class="font-medium">{{ $dateValue ?? '-' }}</span>
                            @if ($timeFieldValue)
                                <span class="ml-2 text-gray-600">{{ $timeFieldValue }} WIB</span>
                            @endif
                        @elseif ($field->type === 'textarea' || $field->type === 'text')
                            <div class="prose max-w-none text-lg leading-relaxed">@markdown($report->data[$field->name] ?? '-')</div>
                        @elseif ($field->type === 'checkbox')
                            <span
                                class="px-3 py-1 rounded-full font-semibold text-sm {{ $report->data[$field->name] ?? false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $report->data[$field->name] ?? false ? 'Ya' : 'Tidak' }}
                            </span>
                        @elseif ($field->type === 'file')
                            @if (!empty($report->data[$field->name]) && Storage::disk('public')->exists($report->data[$field->name]))
                                @php
                                    $path = $report->data[$field->name];
                                    $isImage = in_array(
                                        strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                                        ['jpg', 'jpeg', 'png', 'gif', 'svg'],
                                    );
                                    $fullImageUrl = route('files.serve', ['path' => $path]);
                                @endphp

                                @if ($isImage)
                                    @php
                                        $thumbnailUrl = route('files.serve', ['path' => $path, 'size' => '400x400']);
                                    @endphp
                                    <a href="#"
                                        @click.prevent="$dispatch('open-modal', { imageUrl: '{{ $fullImageUrl }}' })"
                                        class="flex flex-col group flex-shrink-0 mt-2 gap-2">

                                        <!-- Outer Wrapper (final visual size) -->
                                        <div
                                            class="w-40 h-52 overflow-hidden rounded-lg shadow-lg group-hover:opacity-80 transition-opacity border border-gray-200">
                                            <img src="{{ $thumbnailUrl }}"
                                                alt="{{ $field->label }}"
                                                class="w-full h-full object-cover report-image">
                                        </div>

                                        <span
                                            class="text-blue-600 group-hover:underline text-base block mt-1">Lihat
                                            Gambar Penuh</span>

                                    </a>
                                @else
                                    <a href="{{ $fullImageUrl }}" target="_blank"
                                        class="text-blue-600 hover:underline text-base">

                                        Lihat File
                                        ({{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }})

                                    </a>
                                @endif
                            @else
                                <p class="text-gray-500 text-base">
                                    @if (!empty($report->data[$field->name]))
                                        File telah dihapus atau tidak dapat ditemukan.
                                    @else
                                        Tidak ada file yang diunggah.
                                    @endif
                                </p>
                            @endif
                        @else
                            {{-- Fallback for any other field type --}}
                            <span class="text-lg">{{ $report->data[$field->name] ?? '-' }}</span>
                        @endif
                    </div>
                </div>
                @if (!$loop->last)
                    <hr class="my-6">
                @endif
            @endforeach
        </div>
    </div>
</div>
