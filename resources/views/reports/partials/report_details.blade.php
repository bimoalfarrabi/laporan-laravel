<div id="report-detail-container">
    {{-- Simplified Report Metadata --}}
    <div class="space-y-4 mb-6">
        <div class="text-lg">
            <p class="text-gray-600 dark:text-gray-400"><strong>ID Laporan:</strong> <span
                    class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">#{{ $report->id }}</span></p>
            <p class="text-gray-600 dark:text-gray-400"><strong>Jenis Laporan:</strong> <span
                    class="font-semibold text-gray-800 dark:text-gray-200">{{ $report->reportType->name }}</span></p>
            @if ($report->shift)
                <p class="text-gray-600 dark:text-gray-400"><strong>Shift:</strong> <span
                        class="font-semibold text-gray-800 dark:text-gray-200">{{ $report->shift }}</span></p>
            @endif
            <p class="text-gray-600 dark:text-gray-400"><strong>Status:</strong>
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
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">Riwayat Laporan</h3>
        <div
            class="flex flex-col md:flex-row md:justify-between text-base text-gray-700 dark:text-gray-300 space-y-3 md:space-y-0">
            {{-- Left Side --}}
            <div class="space-y-3">
                <p><strong>Dibuat oleh:</strong> {{ $report->user->name }} @if ($report->user->roles->isNotEmpty())
                        <span
                            class="text-sm text-gray-500 dark:text-gray-400">({{ $report->user->roles->first()->name }})</span>
                    @endif pada
                    <span class="font-medium">{{ $report->created_at->format('d-m-Y H:i') }}</span>
                </p>
                @if ($report->lastEditedBy && $report->updated_at != $report->created_at)
                    <p><strong>Terakhir diperbarui oleh:</strong> {{ $report->lastEditedBy->name }}
                        @if ($report->lastEditedBy->roles->isNotEmpty())
                            <span
                                class="text-sm text-gray-500 dark:text-gray-400">({{ $report->lastEditedBy->roles->first()->name }})</span>
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
                                class="text-sm text-gray-500 dark:text-gray-400">({{ $report->approvedBy->roles->first()->name }})</span>
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
                    <p class="text-red-700 dark:text-red-400"><strong>Dihapus oleh:</strong>
                        {{ $report->deletedBy?->name ?? 'Pengguna telah dihapus' }} pada
                        <span class="font-medium">{{ $report->deleted_at->format('d-m-Y H:i') }}</span>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-8">

    {{-- Report Data (single-column with dividers) --}}
    @php
        // Prepare global media list for gallery
        $allMedia = [];
        $tempFields = $report->reportType->reportTypeFields->unique('name');
        foreach ($tempFields as $field) {
            if ($field->type === 'file') {
                $paths = $report->data[$field->name] ?? [];
                if (is_string($paths)) {
                    $paths = [$paths];
                }
                foreach ($paths as $path) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                        $allMedia[] = [
                            'type' => 'image',
                            'url' => route('files.serve', ['path' => $path]),
                            'path' => $path,
                            'name' => basename($path),
                        ];
                    }
                }
            } elseif ($field->type === 'video') {
                $path = $report->data[$field->name] ?? null;
                if ($path) {
                    $allMedia[] = [
                        'type' => 'video',
                        'url' => route('files.serve', ['path' => $path]),
                        'path' => $path,
                        'name' => basename($path),
                    ];
                }
            }
        }
    @endphp

    <div x-data="{
        activeMediaIndex: 0,
        allMedia: @js($allMedia),
        openGallery(index) {
            this.activeMediaIndex = index;
            const media = this.allMedia[index];
            if (media.type === 'image') {
                $dispatch('open-modal', {
                    imageUrl: media.url,
                    fullImageUrl: media.url,
                    reportId: '{{ $report->id }}', // Keep for context if needed, though rotation is gone
                    imagePath: media.path,
                    hasPrev: index > 0,
                    hasNext: index < this.allMedia.length - 1
                });
            } else if (media.type === 'video') {
                $dispatch('open-video-modal', {
                    videoUrl: media.url,
                    videoFileName: media.name,
                    hasPrev: index > 0,
                    hasNext: index < this.allMedia.length - 1
                });
            }
        },
        navigateGallery(direction) {
            let newIndex = this.activeMediaIndex + direction;
            if (newIndex >= 0 && newIndex < this.allMedia.length) {
                // Close current modals
                $dispatch('close-all-modals');
                // Small delay to allow close animation/state reset if needed, 
                // but usually dispatching open immediately is fine if modals handle it.
                // However, our modals use separate components.
                // We'll dispatch a specific close event or just rely on 'open' to override?
                // The modals likely use `show` variable. 
                // Let's add a global listener in the modals to close themselves if another opens?
                // Or just dispatch close-modal/close-video-modal.
    
                // Simpler: Dispatch openGallery with new index. 
                // The modals need to handle switching.
                // Let's modify openGallery to close others explicitly? 
                // Or rely on the components listening to the same event?
                // The components listen to 'open-modal' and 'open-video-modal'.
                // If I open one, I should close the other.
    
                // We'll handle this in the individual modal components to listen for 'close-gallery' or similar.
                // OR simpler:
                this.openGallery(newIndex);
            }
        }
    }" @navigate-gallery.window="navigateGallery($event.detail)">
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
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        @if ($field->name === 'date' || $field->name === 'tanggal')
                            Waktu Kejadian
                        @elseif ($field->type === 'file')
                            Lampiran Gambar
                        @else
                            {{ $field->label }}
                        @endif
                    </h4>
                    <div class="mt-2 text-base text-gray-900 dark:text-gray-100">
                        @if ($field->name === 'date' || $field->name === 'tanggal')
                            @php
                                $dateData = $report->data['date'] ?? ($report->data['tanggal'] ?? null);
                                $dateValue = $dateData ? Carbon\Carbon::parse($dateData)->format('d-m-Y') : null;
                            @endphp
                            <span class="font-medium">{{ $dateValue ?? '-' }}</span>
                            @if ($timeFieldValue)
                                <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $timeFieldValue }} WIB</span>
                            @endif
                        @elseif ($field->type === 'textarea' || $field->type === 'text')
                            <div class="trix-content prose dark:prose-invert max-w-none text-lg leading-relaxed">
                                {!! $report->data[$field->name] ?? '-' !!}
                            </div>
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
                                        @php
                                            $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), [
                                                'jpg',
                                                'jpeg',
                                                'png',
                                                'gif',
                                                'svg',
                                                'webp',
                                            ]);
                                            $fullImageUrl = route('files.serve', ['path' => $path]);

                                            // Find index in allMedia
                                            // Note: $allMedia is built before this loop, so we need to match by path
                                            // This is O(N) inside loop but N is small (number of media items)
                                            $mediaIndex = -1;
                                            if ($isImage) {
                                                foreach ($allMedia as $idx => $m) {
                                                    if ($m['path'] === $path) {
                                                        $mediaIndex = $idx;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if ($isImage)
                                            @php
                                                $thumbnailUrl = route('files.serve', [
                                                    'path' => $path,
                                                    'size' => '200x200',
                                                ]);
                                            @endphp
                                            <a href="#" @click.prevent="openGallery({{ $mediaIndex }})"
                                                x-data="{
                                                    imageLoaded: false,
                                                    init() {
                                                        const img = this.$el.querySelector('img');
                                                        if (img && img.complete && img.naturalWidth > 0) {
                                                            this.imageLoaded = true;
                                                        }
                                                    }
                                                }"
                                                class="group relative block aspect-square w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow-md transition-all duration-200">

                                                <!-- Skeleton Loader -->
                                                <div x-show="!imageLoaded"
                                                    class="absolute inset-0 bg-gray-200 dark:bg-gray-600 animate-pulse flex items-center justify-center">
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
                                                    @load="imageLoaded = true"
                                                    class="h-full w-full object-cover p-2 transition-transform duration-300 group-hover:scale-105 transition-opacity"
                                                    :class="{ 'opacity-0': !imageLoaded }">

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
                                                class="text-blue-600 hover:underline text-base flex items-center justify-center h-40 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                                Lihat File
                                                ({{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }})
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-base">
                                    Tidak ada file yang diunggah.
                                </p>
                            @endif
                        @elseif ($field->type === 'video')
                            @php
                                $videoPath = $report->data[$field->name] ?? null;
                            @endphp

                            @if ($videoPath)
                                @php
                                    // Calculate index for video
                                    $mediaIndex = -1;
                                    foreach ($allMedia as $idx => $m) {
                                        if ($m['path'] === $videoPath) {
                                            $mediaIndex = $idx;
                                            break;
                                        }
                                    }
                                @endphp
                                <div class="mt-2">
                                    {{-- Video Thumbnail with Play Button Overlay --}}
                                    <div class="max-w-3xl" x-data="{
                                        videoLoaded: false,
                                        init() {
                                            const video = this.$el.querySelector('video');
                                            if (video && video.readyState >= 1) {
                                                this.videoLoaded = true;
                                            }
                                        }
                                    }">
                                        <a href="#" @click.prevent="openGallery({{ $mediaIndex }})"
                                            class="group relative block aspect-video w-full overflow-hidden rounded-xl bg-gray-900 border border-gray-300 dark:border-gray-600 shadow-lg hover:shadow-xl transition-all duration-200">

                                            <!-- Skeleton Loader -->
                                            <div x-show="!videoLoaded"
                                                class="absolute inset-0 bg-gray-700 dark:bg-gray-800 animate-pulse flex flex-col items-center justify-center">
                                                <svg class="w-16 h-16 text-gray-500 mb-2" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                                <span class="text-gray-400 text-sm">Loading video...</span>
                                            </div>

                                            {{-- Video Preview (first frame) --}}
                                            <video preload="metadata" @loadeddata="videoLoaded = true"
                                                class="h-full w-full object-cover transition-opacity duration-300"
                                                :class="{ 'opacity-0': !videoLoaded }" muted>
                                                @php
                                                    $extension = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
                                                    $mimeType = match ($extension) {
                                                        'mp4' => 'video/mp4',
                                                        'webm' => 'video/webm',
                                                        'mov', 'qt' => 'video/quicktime',
                                                        'avi' => 'video/x-msvideo',
                                                        'wmv' => 'video/x-ms-wmv',
                                                        default => 'video/' . $extension,
                                                    };
                                                @endphp
                                                <source src="{{ route('files.serve', ['path' => $videoPath]) }}"
                                                    type="{{ $mimeType }}">
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
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        {{ basename($videoPath) }}
                                    </p>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-base">
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
