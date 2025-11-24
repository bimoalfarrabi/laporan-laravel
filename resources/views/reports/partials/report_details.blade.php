<div id="report-detail-container">
    {{-- Simplified Report Metadata --}}
    <div class="space-y-4 mb-6">
        <div class="text-lg">
            <p class="text-gray-600"><strong>ID Laporan:</strong> <span
                    class="font-mono bg-gray-100 px-2 py-1 rounded">#{{ $report->id }}</span></p>
            <p class="text-gray-600"><strong>Jenis Laporan:</strong> <span
                    class="font-semibold text-gray-800">{{ $report->reportType->name }}</span></p>
            @if ($report->shift)
                <p class="text-gray-600"><strong>Shift:</strong> <span
                        class="font-semibold text-gray-800">{{ $report->shift }}</span></p>
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
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $bgColor }}">
                    {{ ucfirst($report->status) }}
                </span>
            </p>
        </div>
    </div>

    {{-- Signature/History Block --}}
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">Riwayat Laporan</h3>
        <div class="flex flex-col md:flex-row md:justify-between text-base text-gray-700 space-y-3 md:space-y-0">
            {{-- Left Side --}}
            <div class="space-y-3">
                <p><strong>Dibuat oleh:</strong> {{ $report->user->name }} @if ($report->user->roles->isNotEmpty())
                        <span class="text-sm text-gray-500">({{ $report->user->roles->first()->name }})</span>
                    @endif pada
                    <span class="font-medium">{{ $report->created_at->format('d-m-Y H:i') }}</span>
                </p>
                @if ($report->lastEditedBy && $report->updated_at != $report->created_at)
                    <p><strong>Terakhir diperbarui oleh:</strong> {{ $report->lastEditedBy->name }}
                        @if ($report->lastEditedBy->roles->isNotEmpty())
                            <span
                                class="text-sm text-gray-500">({{ $report->lastEditedBy->roles->first()->name }})</span>
                        @endif
                        pada
                        <span class="font-medium">{{ $report->updated_at->format('d-m-Y H:i') }}</span>
                    </p>
                @endif
            </div>

            {{-- Right Side --}}
            <div class="space-y-3 md:text-right">
                @if ($report->approvedBy)
                    <p><strong>Disetujui oleh:</strong> {{ $report->approvedBy->name }}
                        @if ($report->approvedBy->roles->isNotEmpty())
                            <span
                                class="text-sm text-gray-500">({{ $report->approvedBy->roles->first()->name }})</span>
                        @endif pada
                        <span class="font-medium">{{ $report->approved_at->format('d-m-Y H:i') }}</span>
                    </p>
                @endif
                @if ($report->rejectedBy)
                    <p><strong>Ditolak oleh:</strong> {{ $report->rejectedBy->name }} pada
                        <span class="font-medium">{{ $report->rejected_at->format('d-m-Y H:i') }}</span>
                    </p>
                @endif
                @if ($report->deleted_at)
                    <p class="text-red-700"><strong>Dihapus oleh:</strong>
                        {{ $report->deletedBy?->name ?? 'Pengguna telah dihapus' }} pada
                        <span class="font-medium">{{ $report->deleted_at->format('d-m-Y H:i') }}</span>
                    </p>
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
                                $dateValue = $dateData ? Carbon\Carbon::parse($dateData)->format('d-m-Y') : null;
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
                            @php
                                $filePaths = $report->data[$field->name] ?? [];
                                if (is_string($filePaths)) {
                                    $filePaths = [$filePaths];
                                }
                            @endphp

                            @if (!empty($filePaths))
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                                    @foreach ($filePaths as $path)
                                        @if (Storage::disk('nextcloud')->exists($path))
                                            @php
                                                $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'svg',
                                                ]);
                                                $fullImageUrl = route('files.serve', ['path' => $path]);
                                            @endphp

                                            @if ($isImage)
                                                @php
                                                    $thumbnailUrl = route('files.serve', [
                                                        'path' => $path,
                                                        'size' => '200x200',
                                                    ]);
                                                @endphp
                                                <a href="#"
                                                    @click.prevent="$dispatch('open-modal', { 
                                                            imageUrl: '{{ route('files.serve', ['path' => $path, 'size' => '800x800']) }}', 
                                                            fullImageUrl: '{{ $fullImageUrl }}',
                                                            reportId: '{{ $report->id }}',
                                                            imagePath: '{{ $path }}'
                                                        })"
                                                    x-data="{ imageLoaded: false }"
                                                    class="group relative block aspect-square w-full overflow-hidden rounded-xl bg-gray-50 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200">

                                                    <!-- Skeleton Loader -->
                                                    <div x-show="!imageLoaded"
                                                        class="absolute inset-0 bg-gray-200 animate-pulse flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>

                                                    <!-- Image -->
                                                    <img src="{{ $thumbnailUrl }}" alt="{{ $field->label }}"
                                                        loading="lazy" data-path="{{ $path }}"
                                                        @load="imageLoaded = true" x-show="imageLoaded"
                                                        x-transition:enter="transition ease-out duration-300"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        class="h-full w-full object-contain p-2 transition-transform duration-300 group-hover:scale-105">

                                                    <!-- Hover Overlay -->
                                                    <div x-show="imageLoaded"
                                                        class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors duration-200 group-hover:bg-black/10">
                                                        <span
                                                            class="opacity-0 transition-opacity duration-200 group-hover:opacity-100 bg-white/90 text-gray-700 px-3 py-1.5 rounded-full text-xs font-medium shadow-sm backdrop-blur-sm">
                                                            Lihat
                                                        </span>
                                                    </div>
                                                </a>
                                            @else
                                                <a href="{{ $fullImageUrl }}" target="_blank"
                                                    class="text-blue-600 hover:underline text-base flex items-center justify-center h-40 border rounded-lg bg-gray-50">
                                                    Lihat File
                                                    ({{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }})
                                                </a>
                                            @endif
                                        @else
                                            <div
                                                class="h-40 border rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 text-sm p-4 text-center">
                                                File tidak ditemukan
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-base">
                                    Tidak ada file yang diunggah.
                                </p>
                            @endif
                        @elseif ($field->type === 'video')
                            @php
                                $videoPath = $report->data[$field->name] ?? null;
                            @endphp

                            @if ($videoPath && Storage::disk('nextcloud')->exists($videoPath))
                                <div class="mt-2">
                                    {{-- Video Thumbnail with Play Button Overlay --}}
                                    <div class="max-w-3xl" x-data="{ videoLoaded: false }">
                                        <a href="#"
                                            @click.prevent="$dispatch('open-video-modal', { 
                                                videoUrl: '{{ route('files.serve', ['path' => $videoPath]) }}',
                                                videoFileName: '{{ basename($videoPath) }}'
                                            })"
                                            class="group relative block aspect-video w-full overflow-hidden rounded-xl bg-gray-900 border border-gray-300 shadow-lg hover:shadow-xl transition-all duration-200">

                                            <!-- Skeleton Loader -->
                                            <div x-show="!videoLoaded"
                                                class="absolute inset-0 bg-gray-700 animate-pulse flex flex-col items-center justify-center">
                                                <svg class="w-16 h-16 text-gray-500 mb-2" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                                <span class="text-gray-400 text-sm">Loading video...</span>
                                            </div>

                                            {{-- Video Preview (first frame) --}}
                                            <video preload="metadata" @loadedmetadata="videoLoaded = true"
                                                x-show="videoLoaded"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100" class="h-full w-full object-cover"
                                                muted>
                                                <source src="{{ route('files.serve', ['path' => $videoPath]) }}#t=0.1"
                                                    type="video/{{ pathinfo($videoPath, PATHINFO_EXTENSION) }}">
                                            </video>

                                            {{-- Play Button Overlay --}}
                                            <div x-show="videoLoaded"
                                                class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors duration-200 group-hover:bg-black/40">
                                                <div
                                                    class="bg-white/95 rounded-full p-4 shadow-2xl transition-transform duration-200 group-hover:scale-110">
                                                    <svg class="w-12 h-12 text-gray-800" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z" />
                                                    </svg>
                                                </div>
                                            </div>

                                            {{-- Hover Text --}}
                                            <div x-show="videoLoaded"
                                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <span class="text-white text-sm font-medium">
                                                    Klik untuk memutar video
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">
                                        {{ basename($videoPath) }}
                                    </p>
                                </div>
                            @else
                                <p class="text-gray-500 text-base">
                                    Video tidak tersedia.
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

<script>
    window.addEventListener('image-rotated', function(e) {
        const path = e.detail.imagePath;
        const timestamp = e.detail.timestamp;

        // Find the thumbnail image with matching data-path
        // Escape backslashes just in case, though unlikely on Linux
        const selector = `img[data-path="${path.replace(/\\/g, '\\\\')}"]`;

        const thumbnail = document.querySelector(selector);
        if (thumbnail) {
            let currentSrc = thumbnail.src;
            // Remove existing timestamp if any to avoid stacking
            currentSrc = currentSrc.replace(/[?&]t=\d+/, '');

            const separator = currentSrc.includes('?') ? '&' : '?';
            thumbnail.src = currentSrc + separator + 't=' + timestamp;
        }
    });
</script>
